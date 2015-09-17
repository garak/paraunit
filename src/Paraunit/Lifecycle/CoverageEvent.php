<?php

namespace Paraunit\Lifecycle;


use Symfony\Component\EventDispatcher\Event;

class CoverageEvent extends Event
{
    const COVERAGE_START = 'coverage.start';
    const COVERAGE_END = 'coverage.end';
}
