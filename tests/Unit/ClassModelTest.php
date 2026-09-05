<?php

namespace Tests\Unit;

use App\Models\ClassModel;
use PHPUnit\Framework\TestCase;

class ClassModelTest extends TestCase
{
    public function test_grade_is_normalized_to_roman_numerals(): void
    {
        $this->assertSame('X', ClassModel::normalizeGrade('10'));
        $this->assertSame('XI', ClassModel::normalizeGrade('xi'));
        $this->assertSame('XII', ClassModel::normalizeGrade('12'));
    }
}
