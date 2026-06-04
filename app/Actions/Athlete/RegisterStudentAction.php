<?php

namespace App\Actions\Athlete;

use App\Mail\StudentWelcomeMail;
use App\Models\Profile;
use App\Models\User;
use App\Support\PersonName;
use App\Support\RegistrationRules;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterStudentAction
{
    /**
     * @param  array  $data
     * @return array{user: User, email_sent: bool}
     */
    public function execute(array $data, int $orgId): array
    {
        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => PersonName::combine($data['first_name'], $data['last_name']),
            'email' => $data['email'],
            'role' => 'student',
            'password' => Hash::make($data['password']),
        ]);

        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => CarbonImmutable::parse($data['birthdate'])->toDateString(),
            'gender' => RegistrationRules::normalizeGender($data['gender']),
            'address' => $data['address'],
            'course' => $data['course'],
            'height_cm' => (float) $data['height_cm'],
            'weight_kg' => (float) $data['weight_kg'],
            'sports_interested' => $data['sports_interested'] ?? [],
        ]);

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['photo']->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        if (! empty($data['sport_ids'])) {
            $user->sports()->sync($data['sport_ids']);
        }

        $emailSent = true;
        try {
            Mail::to($user->email)->send(new StudentWelcomeMail($user));
        } catch (\Throwable $e) {
            Log::error('student_welcome_mail_failed', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
            $emailSent = false;
        }

        return [
            'user' => $user,
            'email_sent' => $emailSent,
        ];
    }
}
