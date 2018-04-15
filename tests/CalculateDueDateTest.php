<?php

include_once __DIR__ . '/../app/CalculateDueDateDate.php';

use PHPUnit\Framework\TestCase;

class CalculateDueDateTest extends TestCase
{
	function testWorkTimeCalculatedIsSuccessInADay()
	{
		$actualTime = $this->generatedActualTime(2018, 4, 12, 11, 22);
		$dueDateCalculate = new CalculateDueDateDate(2, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-12 13:22', '+2 hours');
	}

	function testWorkTimeEndNotOnThatDay()
	{
		$actualTime = $this->generatedActualTime(2018, 4, 12, 11, 22);
		$dueDateCalculate = new CalculateDueDateDate(7, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-13 10:22', '+7 hours');
	}

	function testWorkTimeCalculatedInNotUseWorkDays()
	{
		$actualTime = $this->generatedActualTime(2018, 4, 13, 11, 22);
		$dueDateCalculate = new CalculateDueDateDate(7, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-16 10:22', '+7 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 11, 22);
		$dueDateCalculate = new CalculateDueDateDate(15, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-17 10:22', '+15 hours');

		$actualTime = $this->generatedActualTime(2018, 3, 1, 10, 00);
		$dueDateCalculate = new CalculateDueDateDate(8, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-03-02 10:00', '+8 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 10, 00);
		$dueDateCalculate = new CalculateDueDateDate(32, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-19 10:00', '+32 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 10, 00);
		$dueDateCalculate = new CalculateDueDateDate(48, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-23 10:00', '+48 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 10, 00);
		$dueDateCalculate = new CalculateDueDateDate(47, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-23 09:00', '+47 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 10, 01);
		$dueDateCalculate = new CalculateDueDateDate(47, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-23 09:01', '+47 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 9, 0);
		$dueDateCalculate = new CalculateDueDateDate(8, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-16 09:00', '+8 hours');

		$actualTime = $this->generatedActualTime(2018, 4, 13, 16, 33);
		$dueDateCalculate = new CalculateDueDateDate(1, $actualTime);
		$this->assertEquals($this->generatedAssertDateFormat($dueDateCalculate), '2018-04-16 09:33', '+1 hours');
	}

	function testLessIsWorkStartTime()
	{
		$actualTime = $this->generatedActualTime(2018, 4, 13, 6, 33);
		$dueDateCalculate = new CalculateDueDateDate(1, $actualTime);
		$this->expectException(NotWorkTimeException::class);
		$this->generatedAssertDateFormat($dueDateCalculate);
	}

	function testMoreIsWorkEndTime()
	{
		$actualTime = $this->generatedActualTime(2018, 4, 13, 17, 33);
		$dueDateCalculate = new CalculateDueDateDate(1, $actualTime);
		$this->expectException(NotWorkTimeException::class);
		$this->generatedAssertDateFormat($dueDateCalculate);
	}

	private function generatedAssertDateFormat(CalculateDueDateDate $dueDateCalculate)
	{
		return $dueDateCalculate->getProjectEndTime()->format('Y-m-d H:i');
	}

	private function generatedActualTime(int $year, int $month, int $day, int $hour, int $minute)
	{
		return (new DateTime())->setDate($year, $month, $day)->setTime($hour, $minute);
	}
}