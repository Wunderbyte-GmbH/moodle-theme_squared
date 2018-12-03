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

namespace theme_squared;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/coursecatlib.php');

class coursecat_toolbox extends \coursecat {

    protected static $instance;

    protected function __construct() {
        // Using pseudo category '0', see the 'get' method in the parent.
        $record = new \stdClass();
        $record->id = 0;
        $record->visible = 1;
        $record->depth = 0;
        $record->path = '';
        parent::__construct($record);
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Searches courses
     *
     * List of found course ids is cached for 10 minutes. Cache may be purged prior
     * to this when somebody edits courses or categories, however it is very
     * difficult to keep track of all possible changes that may affect list of courses.
     *
     * Adapted from coursecatlib.php search_courses to add category id.
     *
     * @param string $search search string or empty for all within a given category if specified.
     * @param array $options display options, same as in get_courses() except 'recursive' and 'idonly' is ignored -
     *                       search can be within a category if 'categoryid' is specified.
     * @param array $requiredcapabilities List of capabilities required to see return course.
     * @return array ('totalcount' => int, 'courses' => course_in_list[]).
     */
    public static function search_courses($search = '', $options = array(), $requiredcapabilities = array()) {
        global $DB;
        $categoryid = !empty($options['categoryid']) ? $options['categoryid'] : 0;
        $limit = !empty($options['limit']) ? $options['limit'] : null;
        $offset = !empty($options['offset']) ? $options['offset'] : 0;
        $sortfields = !empty($options['sort']) ? $options['sort'] : array('sortorder' => 1); // Note: 1 is ASC and -1 is DESC, see get_courses().

        $coursecatcache = \cache::make('theme_squared', 'coursecat');
        $cachekey = 's-' . serialize(
                        array($search) + array('categoryid' => $categoryid) + array('sort' => $sortfields) + array('requiredcapabilities' => $requiredcapabilities)
        );
        $cntcachekey = 'scnt-' . serialize($search);

        $ids = $coursecatcache->get($cachekey);
        if ($ids !== false) {
            // We already cached last search result.
            $totalcount = count($ids);
            $ids = array_slice($ids, $offset, $limit);
            $courses = array();
            if (!empty($ids)) {
                list($sql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id');
                $records = self::get_course_records("c.id " . $sql, $params, $options);
                // Preload course contacts if necessary - saves DB queries later to do it for each course separately.
                if (!empty($options['coursecontacts'])) {
                    self::preload_course_contacts($records);
                }
                // Prepare the list of course_in_list objects.
                foreach ($ids as $id) {
                    $courses[$id] = new \course_in_list($records[$id]);
                }
            }
            return array(
                'totalcount' => $totalcount,
                'courses' => $courses
            );
        }

        $preloadcoursecontacts = !empty($options['coursecontacts']);
        unset($options['coursecontacts']);

        // Search courses that have specified words in their names/summaries.
        $searchterms = preg_split('|\s+|', trim($search), 0, PREG_SPLIT_NO_EMPTY);

        $courselist = get_courses_search($searchterms, 'c.sortorder ASC', 0, 9999999, $totalcount, $requiredcapabilities);

        // With category id the don't really want to have a custom version of 'get_courses_search()' in datalib.php, so filter here.
        if ($categoryid) {
            foreach ($courselist as $coursekey => $courseentry) {
                if ($courseentry->category != $categoryid) {
                    unset($courselist[$coursekey]);
                }
            }
        }

        self::sort_records($courselist, $sortfields);
        $coursecatcache->set($cachekey, array_keys($courselist));
        $coursecatcache->set($cntcachekey, $totalcount);
        $records = array_slice($courselist, $offset, $limit, true);

        // Preload course contacts if necessary - saves DB queries later to do it for each course separately.
        if (!empty($preloadcoursecontacts)) {
            self::preload_course_contacts($records);
        }

        // Prepare the list of course_in_list objects.
        $courses = array();
        foreach ($records as $record) {
            $courses[$record->id] = new \course_in_list($record);
        }

        return array(
            'totalcount' => $totalcount,
            'courses' => $courses
        );
    }

    /**
     * Gets the image url for the given course.
     * 
     * @param course_in_list|stdClass $course The course to use.
     * @param string $for 'course'|'overview'|empty Specify the image to get if any.
     */
    public static function course_image($course, $for = '') {
        $courseimageurl = null;
        $candidates = array();
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if ($isimage) {
                $filename = pathinfo($file->get_filename(), PATHINFO_FILENAME);
                $candidates[$filename] = $file;
            }
        }

        if (!empty($candidates)) {
            global $CFG;
            $file = null;
            if (!empty($for)) {
                $use = null;
                foreach ($candidates as $candidatekey => $candidate) {
                    if (strcmp($candidatekey, $for) === 0) { // Does this need to be multibyte (UTF8) safe?
                        $file = $candidate;
                        break;
                    }
                }
                if (empty($file)) {
                    // Use the first.
                    $file = reset($candidates);
                }
            } else {
                // Use the first.
                $file = reset($candidates);
            }
            $courseimageurl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
        }

        if (empty($courseimageurl)) {
            $pattern = new \core_geopattern();
            $pattern->setColor(self::coursecolour($course->id));
            $pattern->setGenerator('squares');
            $courseimageurl = $pattern->datauri();
        }

        return $courseimageurl;
    }

    /**
     * Generate a semi-random colour based on the courseid number (so it will always return
     * the same colour for a course).
     * 
     * Code from /blocks/myoverview/classses/output/courses_view.php.
     *
     * @param int $courseid.
     * @return string Hex value colour code.
     */
    protected static function coursecolour($courseid) {
        // The colour palette is hardcoded for now.  It would make sense to combine it with theme settings.
        static $basecolours = [
            '#81ecec', '#74b9ff', '#a29bfe', '#dfe6e9', '#00b894', '#0984e3', '#b2bec3', '#fdcb6e', '#fd79a8', '#6c5ce7', '#ffaabb'];

        return $basecolours[$courseid % 11];
    }
}
