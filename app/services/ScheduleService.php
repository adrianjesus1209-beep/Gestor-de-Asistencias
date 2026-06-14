<?php
namespace App\Services;

class ScheduleService {
    public function format($rawSchedule) {
        $schedule = [
            "Monday" => [], "Tuesday" => [], "Wednesday" => [], 
            "Thursday" => [], "Friday" => [], "Saturday" => []
        ];

        foreach ($rawSchedule as $row) {
            $day = $row['day_of_week'];
            if (isset($schedule[$day])) {
                $schedule[$day][] = [
                    "subject" => $row['subject_name'],
                    "section" => $row['section_name'],
                    "start"   => substr($row['start_time'], 0, 5), 
                    "end"     => substr($row['end_time'], 0, 5)
                ];
            }
        }
        return $schedule;
    }
}