<?php

namespace Symbiote\GridFieldExtensions\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\Tests\Stub\StubA;
use Symbiote\GridFieldExtensions\Tests\Stub\StubB;

/**
 * Tests for {@link GridFieldAddNewMultiClass}.
 */
class GridFieldAddNewMultiClassTest extends SapphireTest
{
    public function testGetClasses()
    {
        $grid = new GridField('TestGridField');
        $grid->setModelClass(StubA::class);

        $component = new GridFieldAddNewMultiClass();

        $this->assertEquals(
            array(
                'Symbiote-GridFieldExtensions-Tests-Stub-StubA' => 'A',
                'Symbiote-GridFieldExtensions-Tests-Stub-StubB' => 'B',
                'Symbiote-GridFieldExtensions-Tests-Stub-StubC' => 'C'
            ),
            $component->getClasses($grid),
            'Subclasses are populated by default and sorted'
        );

        $component->setClasses(array(
            StubB::class => 'Custom Title',
            StubA::class
        ));

        $this->assertEquals(
            array(
                'Symbiote-GridFieldExtensions-Tests-Stub-StubB' => 'Custom Title',
                'Symbiote-GridFieldExtensions-Tests-Stub-StubA' => 'A'
            ),
            $component->getClasses($grid),
            'Sorting and custom titles can be specified'
        );
    }
}
