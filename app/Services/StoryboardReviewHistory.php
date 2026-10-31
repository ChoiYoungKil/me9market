<?php

namespace App\Services;

class StoryboardReviewHistory
{
    /** Historical observations only; this reader must not create or replay review data. */
    public function all(): array
    {
        $archive = json_decode(file_get_contents(resource_path('storyboard/review-history.json')), true, 512, JSON_THROW_ON_ERROR);
        $records = [];

        foreach ($archive['pages'] as $number => $page) {
            $group = $archive['groups'][$page['group']];
            $records[(int) $number] = [
                'date' => $group['date'],
                'environment' => $archive['environment'],
                'scope' => $page['record_scope'] ?? $group['scope'],
                'precondition' => $group['precondition'],
                'cases' => $page['cases'],
                'corrections' => $page['corrections'],
                'automation' => $group['automation'],
                'automation_scope' => $group['scope'],
                'limitations' => array_merge($group['limitations'], $page['limitations']),
                'sources' => $group['source'],
            ];
        }

        return $records;
    }
}
