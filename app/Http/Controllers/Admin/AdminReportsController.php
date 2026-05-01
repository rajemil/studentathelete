<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportsController extends Controller
{
    public function __invoke(): View
    {
        $orgId = auth()->user()->organization_id;
        $now = CarbonImmutable::now();

        $summary = [
            'students' => User::query()->where('organization_id', $orgId)->where('role', 'student')->count(),
            'coaches' => User::query()->where('organization_id', $orgId)->where('role', 'coach')->count(),
            'instructors' => User::query()->where('organization_id', $orgId)->where('role', 'instructor')->count(),
            'sports' => Sport::query()->where('organization_id', $orgId)->count(),
            'teams' => Team::query()->where('organization_id', $orgId)->count(),
            'scores_30d' => PerformanceScore::query()
                ->whereHas('sport', fn ($q) => $q->where('organization_id', $orgId))
                ->whereNotNull('scored_on')
                ->where('scored_on', '>=', $now->subDays(30)->toDateString())
                ->count(),
        ];

        return view('admin.reports', compact('summary'));
    }

    public function export(Request $request): StreamedResponse
    {
        $orgId = $request->user()->organization_id;
        $now = CarbonImmutable::now();

        $validated = $request->validate([
            'since' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'category' => ['nullable', 'string', 'max:64'],
        ]);

        $since = CarbonImmutable::parse($validated['since'] ?? $now->subDays(90)->toDateString())->toDateString();
        $until = CarbonImmutable::parse($validated['until'] ?? $now->toDateString())->toDateString();
        if ($since > $until) {
            [$since, $until] = [$until, $since];
        }

        $sportId = null;
        if (isset($validated['sport_id'])) {
            $sportIdValue = Sport::query()
                ->where('organization_id', $orgId)
                ->whereKey((int) $validated['sport_id'])
                ->value('id');
            $sportId = $sportIdValue !== null ? (int) $sportIdValue : null;
        }

        $studentId = null;
        if (isset($validated['student_id'])) {
            $studentIdValue = User::query()
                ->where('organization_id', $orgId)
                ->where('role', 'student')
                ->whereKey((int) $validated['student_id'])
                ->value('id');
            $studentId = $studentIdValue !== null ? (int) $studentIdValue : null;
        }

        $category = isset($validated['category']) && $validated['category'] !== '' ? (string) $validated['category'] : null;

        $filename = 'performance-scores-'.$now->format('Y-m-d').'.csv';

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'organization_id' => $orgId,
                'since' => $since,
                'until' => $until,
                'sport_id' => $sportId,
                'student_id' => $studentId,
                'category' => $category,
            ])
            ->log('report_export_performance_scores');

        return Response::streamDownload(function () use ($orgId, $since, $until, $sportId, $studentId, $category): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, [
                'scored_on',
                'student_id',
                'student_name',
                'student_email',
                'sport_id',
                'sport_name',
                'category',
                'score',
            ]);

            PerformanceScore::query()
                ->select([
                    'performance_scores.scored_on',
                    'performance_scores.user_id',
                    'performance_scores.sport_id',
                    'performance_scores.category',
                    'performance_scores.score',
                    'users.name as student_name',
                    'users.email as student_email',
                    'sports.name as sport_name',
                ])
                ->join('users', 'users.id', '=', 'performance_scores.user_id')
                ->join('sports', 'sports.id', '=', 'performance_scores.sport_id')
                ->where('users.organization_id', $orgId)
                ->where('sports.organization_id', $orgId)
                ->whereNotNull('performance_scores.scored_on')
                ->whereDate('performance_scores.scored_on', '>=', $since)
                ->whereDate('performance_scores.scored_on', '<=', $until)
                ->when($sportId !== null, fn ($q) => $q->where('performance_scores.sport_id', $sportId))
                ->when($studentId !== null, fn ($q) => $q->where('performance_scores.user_id', $studentId))
                ->when($category !== null, fn ($q) => $q->where('performance_scores.category', $category))
                ->orderByDesc('performance_scores.scored_on')
                ->orderByDesc('performance_scores.id')
                ->chunk(500, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            (string) $row->scored_on,
                            (string) $row->user_id,
                            (string) ($row->student_name ?? ''),
                            (string) ($row->student_email ?? ''),
                            (string) $row->sport_id,
                            (string) ($row->sport_name ?? ''),
                            (string) $row->category,
                            (string) $row->score,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
