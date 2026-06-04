<?php

namespace App\Services\Athlete;

use App\Actions\Athlete\RegisterStudentAction;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Support\RegistrationRules;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentWelcomeMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AthleteManagementService
{
    protected RegisterStudentAction $registerStudentAction;

    public function __construct(RegisterStudentAction $registerStudentAction)
    {
        $this->registerStudentAction = $registerStudentAction;
    }

    /**
     * Register a student athlete using the Action.
     *
     * @param  array  $data
     * @param  int  $orgId
     * @param  User  $actor
     * @return array{user: User, code: string, email_sent: bool}
     */
    public function register(array $data, int $orgId, User $actor): array
    {
        $result = $this->registerStudentAction->execute($data, $orgId);

        activity()
            ->performedOn($result['user'])
            ->causedBy($actor)
            ->log('student_created');

        return $result;
    }

    /**
     * Update an existing student athlete.
     *
     * @param  User  $student
     * @param  array  $data
     * @param  int  $orgId
     * @param  User  $actor
     * @return User
     */
    public function update(User $student, array $data, int $orgId, User $actor): User
    {
        $student->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $student->update(['password' => Hash::make($data['password'])]);
        }

        $allowedSportIds = $this->allowedSportIds($orgId);
        $interestedIds = $this->filterSportIds($data['sports_interested'] ?? [], $allowedSportIds);
        $enrolledIds = $this->filterSportIds($data['sport_ids'] ?? [], $allowedSportIds);

        $birthdate = isset($data['birthdate']) ? CarbonImmutable::parse($data['birthdate']) : null;

        $height = isset($data['height_cm']) ? (float) $data['height_cm'] : null;
        $weight = isset($data['weight_kg']) ? (float) $data['weight_kg'] : null;

        $profile = $student->profile ?: Profile::query()->create(['user_id' => $student->id]);
        $profile->fill([
            'birthdate' => $birthdate?->toDateString(),
            'gender' => isset($data['gender']) ? RegistrationRules::normalizeGender($data['gender']) : null,
            'address' => $data['address'] ?? null,
            'course' => $data['course'] ?? $profile->course,
            'height_cm' => $height,
            'weight_kg' => $weight,
            'sports_interested' => $interestedIds,
        ])->save();

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $path = $data['photo']->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $student->sports()->sync($enrolledIds);

        activity()
            ->performedOn($student)
            ->causedBy($actor)
            ->log('student_updated');

        return $student;
    }

    /**
     * Delete a student athlete and their photo.
     *
     * @param  User  $student
     * @param  User  $actor
     * @return void
     */
    public function destroy(User $student, User $actor): void
    {
        if ($student->profile?->photo_path) {
            Storage::disk('public')->delete($student->profile->photo_path);
        }

        activity()
            ->performedOn($student)
            ->causedBy($actor)
            ->log('student_deleted');

        $student->delete();
    }

    /**
     * Import students from a CSV file.
     *
     * @param  string  $filePath
     * @param  int  $orgId
     * @param  User  $actor
     * @return array{created: int, skipped: int, report: array}
     * @throws \Exception
     */
    public function importCsv(string $filePath, int $orgId, User $actor): array
    {
        $allowedSportIds = $this->allowedSportIds($orgId);
        $sportsByName = Sport::query()
            ->where('organization_id', $orgId)
            ->get()
            ->keyBy(fn (Sport $s) => mb_strtolower(trim($s->name)))->all();

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception('Could not read the CSV file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \Exception('The CSV file is empty.');
        }

        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $header);
        $col = array_flip($header);

        foreach (['name', 'email'] as $required) {
            if (! isset($col[$required])) {
                fclose($handle);
                throw new \Exception('CSV must include columns: name, email (optional: birthdate, gender, height_cm, weight_kg, sports).');
            }
        }

        $created = 0;
        $skipped = 0;
        $report = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }

            $name = trim((string) ($row[$col['name']] ?? ''));
            $email = mb_strtolower(trim((string) ($row[$col['email']] ?? '')));
            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (User::query()->where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $birthdateRaw = isset($col['birthdate']) ? trim((string) ($row[$col['birthdate']] ?? '')) : '';
            $genderRaw = isset($col['gender']) ? mb_strtolower(trim((string) ($row[$col['gender']] ?? ''))) : '';
            $heightRaw = isset($col['height_cm']) ? trim((string) ($row[$col['height_cm']] ?? '')) : '';
            $weightRaw = isset($col['weight_kg']) ? trim((string) ($row[$col['weight_kg']] ?? '')) : '';
            $sportsRaw = isset($col['sports']) ? trim((string) ($row[$col['sports']] ?? '')) : '';

            $gender = in_array($genderRaw, ['male', 'female', 'other', 'prefer_not_to_say'], true) ? $genderRaw : null;
            $birthdate = $birthdateRaw !== '' ? CarbonImmutable::parse($birthdateRaw) : null;
            $height = $heightRaw !== '' ? (float) $heightRaw : null;
            $weight = $weightRaw !== '' ? (float) $weightRaw : null;

            $enrolledIds = [];
            if ($sportsRaw !== '') {
                foreach (preg_split('/[,;|]/', $sportsRaw) as $piece) {
                    $key = mb_strtolower(trim($piece));
                    if ($key !== '' && isset($sportsByName[$key])) {
                        $enrolledIds[] = (int) $sportsByName[$key]->id;
                    }
                }
                $enrolledIds = $this->filterSportIds($enrolledIds, $allowedSportIds);
            }

            $tempPassword = \Illuminate\Support\Str::password(16);

            $user = User::query()->create([
                'organization_id' => $orgId,
                'name' => $name,
                'email' => $email,
                'role' => 'student',
                'password' => Hash::make($tempPassword),
            ]);

            Profile::query()->create([
                'user_id' => $user->id,
                'birthdate' => $birthdate?->toDateString(),
                'gender' => $gender,
                'address' => null,
                'height_cm' => $height,
                'weight_kg' => $weight,
                'sports_interested' => $enrolledIds,
            ]);

            $user->sports()->sync($enrolledIds);

            activity()
                ->performedOn($user)
                ->causedBy($actor)
                ->log('student_created');

            $user->sendEmailVerificationNotification();

            try {
                Mail::to($user->email)->send(new StudentWelcomeMail($user));
            } catch (\Throwable $e) {
                Log::error('student_import_welcome_mail_failed', ['user_id' => $user->id, 'exception' => $e->getMessage()]);
            }

            if (count($report) < 200) {
                $report[] = ['email' => $user->email, 'status' => 'created'];
            }

            $created++;
        }

        fclose($handle);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'report' => $report,
        ];
    }

    /**
     * @return list<int>
     */
    protected function allowedSportIds(int $orgId): array
    {
        return Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @param  list<int>  $allowed
     * @return list<int>
     */
    protected function filterSportIds(array $ids, array $allowed): array
    {
        $allowedSet = array_flip($allowed);

        return collect($ids)
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => isset($allowedSet[$id]))
            ->unique()
            ->values()
            ->all();
    }
}
