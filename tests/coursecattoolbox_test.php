<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the squared theme.
 *
 *
 * The squared theme makes uses a custom version of squared blocks
 *
 * @package theme_squared
 * @copyright 2018 onwards Onlinecampus Virtuelle PH
 * www.virtuelle-ph.at, David Bogner www.edulabs.org
 * @author G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Toolbox unit tests for the Squared theme.
 * @group theme_squared
 */
class theme_squared_coursecattoolbox_testcase extends advanced_testcase {

    private $testcategory;
    private $toolbox;

    protected function setUp() {
        $this->resetAfterTest(true);

        set_config('theme', 'squared');

        $this->toolbox = \theme_squared\coursecat_toolbox::get_instance();

        // Create a category.
        $category = new stdClass();
        $category->name = 'The Category';

        $this->testcategory = $this->getDataGenerator()->create_category($category);

        // Create a course.
        $course = new stdClass();
        $course->fullname = 'Topic One';
        $course->shortname = 'TP1';
        $course->idnumber = '007';
        $course->summary = 'Mr Bond!';
        $course->summaryformat = FORMAT_PLAIN;
        $course->format = 'topics';
        $course->newsitems = 0;
        $course->numsections = 7;
        $course->category = $this->testcategory->id;
        $this->getDataGenerator()->create_course($course);

        $course->fullname = 'Topic Two';
        $course->shortname = 'TP2';
        $course->idnumber = '017';
        $this->getDataGenerator()->create_course($course);
        $course->fullname = 'Topic Three';
        $course->shortname = 'TP3';
        $course->idnumber = '027';
        $this->getDataGenerator()->create_course($course);

        // Create another category that we don't need to filter on.
        $category->name = 'Another Category';
        $anothercategory = $this->getDataGenerator()->create_category($category);

        $course->category = $anothercategory->id;
        $course->fullname = 'Topic Four';
        $course->shortname = 'TP4';
        $course->idnumber = '037';
        $this->getDataGenerator()->create_course($course);
        $course->fullname = 'Topic Five';
        $course->shortname = 'TP5';
        $course->idnumber = '047';
        $this->getDataGenerator()->create_course($course);
    }

    protected function add_more_courses() {
        // Create a course.
        $course = new stdClass();
        $course->fullname = 'Three One';
        $course->shortname = 'TP31';
        $course->idnumber = '0171';
        $course->summary = 'Mr Bond!';
        $course->summaryformat = FORMAT_PLAIN;
        $course->format = 'topics';
        $course->newsitems = 0;
        $course->numsections = 7;
        $course->category = $this->testcategory->id;
        $this->getDataGenerator()->create_course($course);

        $course->fullname = 'Three Two';
        $course->shortname = 'TP32';
        $course->idnumber = '0172';
        $this->getDataGenerator()->create_course($course);

        $course->fullname = 'Three Three';
        $course->shortname = 'TP33';
        $course->idnumber = '0173';
        $this->getDataGenerator()->create_course($course);

        $category = new stdClass();
        $category->name = 'Another another Category';
        $anothercategory = $this->getDataGenerator()->create_category($category);
        $course->category = $anothercategory->id;
        $course->fullname = 'Three Four';
        $course->shortname = 'TP34';
        $course->idnumber = '0174';
        $this->getDataGenerator()->create_course($course);
        $course->fullname = 'Three Five';
        $course->shortname = 'TP35';
        $course->idnumber = '0175';
        $this->getDataGenerator()->create_course($course);
    }

    public function test_search_all_courses() {
        $searchresults = $this->toolbox->search_courses()['courses'];
        $expectedresults = array(
            'TP1' => 'Topic One',
            'TP2' => 'Topic Two',
            'TP3' => 'Topic Three',
            'TP4' => 'Topic Four',
            'TP5' => 'Topic Five'
        );

        $this->assertEquals(count($searchresults), 5);
        $expectedresultcount = 0;
        foreach ($searchresults as $courseinlistobject) {
            $this->assertEquals($courseinlistobject->fullname, $expectedresults[$courseinlistobject->shortname]);
            $expectedresultcount++;
        }
        $this->assertEquals($expectedresultcount, 5);
    }

    public function test_search_some_courses() {
        $searchresults = $this->toolbox->search_courses('Three')['courses'];
        $expectedresults = array(
            'TP3' => 'Topic Three'
        );

        $this->assertEquals(count($searchresults), 1);
        $expectedresultcount = 0;
        foreach ($searchresults as $courseinlistobject) {
            $this->assertEquals($courseinlistobject->fullname, $expectedresults[$courseinlistobject->shortname]);
            $expectedresultcount++;
        }
        $this->assertEquals($expectedresultcount, 1);
    }

    public function test_search_course_category_one() {
        $searchresults = $this->toolbox->search_courses('', array('categoryid' => $this->testcategory->id))['courses'];
        $expectedresults = array(
            'TP1' => 'Topic One',
            'TP2' => 'Topic Two',
            'TP3' => 'Topic Three'
        );

        $this->assertEquals(count($searchresults), 3);
        $expectedresultcount = 0;
        foreach ($searchresults as $courseinlistobject) {
            $this->assertEquals($courseinlistobject->fullname, $expectedresults[$courseinlistobject->shortname]);
            $expectedresultcount++;
        }
        $this->assertEquals($expectedresultcount, 3);
    }

    public function test_search_course_category_two() {
        $this->add_more_courses();
        $searchresults = $this->toolbox->search_courses('Three')['courses'];
        $expectedresults = array(
            'TP3' => 'Topic Three',
            'TP31' => 'Three One',
            'TP32' => 'Three Two',
            'TP33' => 'Three Three',
            'TP34' => 'Three Four',
            'TP35' => 'Three Five'
        );

        $this->assertEquals(count($searchresults), 6);
        $expectedresultcount = 0;
        foreach ($searchresults as $courseinlistobject) {
            $this->assertEquals($courseinlistobject->fullname, $expectedresults[$courseinlistobject->shortname]);
            $expectedresultcount++;
        }
        $this->assertEquals($expectedresultcount, 6);
    }

    public function test_search_course_category_three() {
        $this->add_more_courses();
        $searchresults = $this->toolbox->search_courses('Three', array('categoryid' => $this->testcategory->id))['courses'];
        $expectedresults = array(
            'TP3' => 'Topic Three',
            'TP31' => 'Three One',
            'TP32' => 'Three Two',
            'TP33' => 'Three Three'
        );

        $this->assertEquals(count($searchresults), 4);
        $expectedresultcount = 0;
        foreach ($searchresults as $courseinlistobject) {
            $this->assertEquals($courseinlistobject->fullname, $expectedresults[$courseinlistobject->shortname]);
            $expectedresultcount++;
        }
        $this->assertEquals($expectedresultcount, 4);
    }

}