<?php

interface ProjectDateTimeCalculate
{
	public function __construct(int $workHours, DateTime $actualTime);
	public function getProjectEndTime(): DateTime;
}

class CalculateDueDateDate implements ProjectDateTimeCalculate
{
	private $workHours;
	private $actualTime;

	CONST WORK_START_HOUR = 9;
	CONST WORK_END_HOUR = 17;
	CONST NO_WORK_DAY = [6, 7];

	public function __construct(int $workHours, DateTime $actualTime)
	{
		$this->workHours = $workHours;
		$this->actualTime = $actualTime;
	}

	public function getProjectEndTime(): DateTime
	{
		$projectTime = $this->actualTime;
		$workDaysByHours = $this->getWorkDaysByHours($this->workHours);
		$projectTime = $this->modifyTimeIfThereAreWorkDays($workDaysByHours, $projectTime);
		$projectTime = $this->modifyTimeIfThereAreNotWorkingDays((bool)$workDaysByHours, $projectTime);
		$projectTime = $this->modifyTimeIfProjectEndHoursMoreThanTheWorkEndTime($projectTime);
		$projectTime = $this->modifyTimeIfProjectEndHoursLessThanTheWorkEndTime($projectTime);
		return $this->modifyDayIfWeekendDay($projectTime);
	}

	private function modifyDayIfWeekendDay(DateTime $projectTime): DateTime
	{
		$dayNumber = (int)$projectTime->format("N");
		if ($dayNumber === 6) {
			$projectTime->modify("+2 days");
		}
		if ($dayNumber === 7) {
			$projectTime->modify("+1 days");
		}

		return $projectTime;
	}

	private function modifyTimeIfProjectEndHoursMoreThanTheWorkEndTime(DateTime $projectTime): DateTime
	{
		$hour = (int)$projectTime->format("H");
		$minute = $projectTime->format("i");

		if ($hour >= self::WORK_END_HOUR) {
			$dayPlusHours = $hour - self::WORK_END_HOUR;
			$projectTime->modify("+1 days");
			$projectTime->setTime(self::WORK_START_HOUR, $minute);
			$projectTime->modify("+$dayPlusHours hours");
		}

		return $projectTime;
	}

	private function modifyTimeIfProjectEndHoursLessThanTheWorkEndTime(DateTime $projectTime): DateTime
	{
		$hour = (int)$projectTime->format("H");
		$minute = $projectTime->format("i");

		if ($hour < self::WORK_START_HOUR) {
			$timeDiffer = $projectTime->diff($projectTime->setTime(self::WORK_END_HOUR, 00));
			$addDays = $this->getWorkDaysByHours($timeDiffer->h);
			$projectTime->setTime(self::WORK_START_HOUR, $minute);

			if ($addDays) {
				$projectTime->modify("+$addDays days");
			}

			$plusHours = $timeDiffer->h - $addDays * $this->workHoursInOneDay();
			$projectTime->modify("+$plusHours hours");
		}

		return $projectTime;
	}

	private function modifyTimeIfThereAreWorkDays(int $workDaysByHours, DateTime $projectTime): DateTime
	{
		if ($workDaysByHours) {
			for ($i = 0; $i < $workDaysByHours; $i++) {
				$projectTime->modify("+1 days");
				$projectTime = $this->modifyDayIfWeekendDay($projectTime);
			}

			$addHours = $this->workHours - $workDaysByHours * $this->workHoursInOneDay();
			if ($addHours) {
				$projectTime->modify("+$addHours hours");
			}
		}

		return $projectTime;
	}

	private function modifyTimeIfThereAreNotWorkingDays(bool $workDaysByHours, DateTime $projectTime): DateTime
	{
		if (!$workDaysByHours) {
			$projectTime->modify("+$this->workHours hours");
		}

		return $projectTime;
	}

	private function workHoursInOneDay(): int
	{
		return self::WORK_END_HOUR - self::WORK_START_HOUR;
	}

	private function getWorkDaysByHours(int $hours): int
	{
		return (int)round($hours / $this->workHoursInOneDay(), PHP_ROUND_HALF_DOWN);
	}
}