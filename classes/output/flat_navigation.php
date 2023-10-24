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
 * Squared theme.
 *
 * @package    theme_squared
 * @copyright  &copy; 2023-onwards G J Barnard.  Based upon work by Damyon Wiese.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace theme_squared\output;

use moodle_url;
use navigation_node;
use navigation_node_collection;
use pix_icon;

/**
 * Subclass of navigation_node allowing different rendering for the flat navigation
 * in particular allowing dividers and indents.
 *
 * @package   core
 * @category  navigation
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class flat_navigation_node extends navigation_node {

    /** @var $indent integer The indent level */
    private $indent = 0;

    /** @var $showdivider bool Show a divider before this element */
    private $showdivider = false;

    /** @var $collectionlabel string Label for a group of nodes */
    private $collectionlabel = '';

    /**
     * A proxy constructor
     *
     * @param mixed $navnode A navigation_node or an array
     */
    public function __construct($navnode, $indent) {
        if (is_array($navnode)) {
            parent::__construct($navnode);
        } else if ($navnode instanceof navigation_node) {

            // Just clone everything.
            $objvalues = get_object_vars($navnode);
            foreach ($objvalues as $key => $value) {
                 $this->$key = $value;
            }
        } else {
            throw new coding_exception('Not a valid flat_navigation_node');
        }
        $this->indent = $indent;
    }

    /**
     * Setter, a label is required for a flat navigation node that shows a divider.
     *
     * @param string $label
     */
    public function set_collectionlabel($label) {
        $this->collectionlabel = $label;
    }

    /**
     * Getter, get the label for this flat_navigation node, or it's parent if it doesn't have one.
     *
     * @return string
     */
    public function get_collectionlabel() {
        if (!empty($this->collectionlabel)) {
            return $this->collectionlabel;
        }
        if ($this->parent && ($this->parent instanceof flat_navigation_node || $this->parent instanceof flat_navigation)) {
            return $this->parent->get_collectionlabel();
        }
        debugging('Squared Flat Navigation region requires a label', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Does this node represent a course section link.
     * @return boolean
     */
    public function is_section() {
        return $this->type == navigation_node::TYPE_SECTION;
    }

    /**
     * In flat navigation - sections are active if we are looking at activities in the section.
     * @return boolean
     */
    public function isactive() {
        global $PAGE;

        if ($this->is_section()) {
            $active = $PAGE->navigation->find_active_node();
            if ($active) {
                while ($active = $active->parent) {
                    if ($active->key == $this->key && $active->type == $this->type) {
                        return true;
                    }
                }
            }
        }
        return $this->isactive;
    }

    /**
     * Getter for "showdivider"
     * @return boolean
     */
    public function showdivider() {
        return $this->showdivider;
    }

    /**
     * Setter for "showdivider"
     * @param $val boolean
     * @param $label string Label for the group of nodes
     */
    public function set_showdivider($val, $label = '') {
        $this->showdivider = $val;
        if ($this->showdivider && empty($label)) {
            debugging('Squared Flat Navigation region requires a label', DEBUG_DEVELOPER);
        } else {
            $this->set_collectionlabel($label);
        }
    }

    /**
     * Getter for "indent"
     * @return boolean
     */
    public function get_indent() {
        return $this->indent;
    }

    /**
     * Setter for "indent"
     * @param $val boolean
     */
    public function set_indent($val) {
        $this->indent = $val;
    }
}

/**
 * Class used to generate a collection of navigation nodes most closely related
 * to the current page.
 *
 * @deprecated since Moodle 4.0 - do not use any more. Leverage secondary/tertiary navigation concepts
 * @package core
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class flat_navigation extends navigation_node_collection {
    /** @var moodle_page the moodle page that the navigation belongs to */
    protected $page;

    /**
     * Constructor.
     *
     * @param moodle_page $page
     */
    public function __construct(\moodle_page &$page) {
        if (during_initial_install()) {
            return false;
        }
        $this->page = $page;
    }

    /**
     * Build the list of navigation nodes based on the current navigation and settings trees.
     *
     */
    public function initialise() {
        global $CFG, $OUTPUT, $PAGE, $USER;
        if (during_initial_install()) {
            return;
        }

        $current = false;

        $course = $PAGE->course;

        $this->page->navigation->initialise();

        // First walk the nav tree looking for "flat_navigation" nodes.
        if ($course->id > 1) {
            // It's a real course.
            $url = new moodle_url('/course/view.php', array('id' => $course->id));

            $coursecontext = \context_course::instance($course->id, MUST_EXIST);
            $displaycontext = \context_helper::get_navigation_filter_context($coursecontext);
            // This is the name that will be shown for the course.
            $coursename = empty($CFG->navshowfullcoursenames) ?
                format_string($course->shortname, true, ['context' => $displaycontext]) :
                format_string($course->fullname, true, ['context' => $displaycontext]);

            $flat = new flat_navigation_node(navigation_node::create($coursename, $url), 0);
            $flat->set_collectionlabel($coursename);
            $flat->key = 'coursehome';
            $flat->icon = new pix_icon('i/course', '');

            $courseformat = course_get_format($course);
            $coursenode = $PAGE->navigation->find_active_node();
            $targettype = navigation_node::TYPE_COURSE;

            // Single activity format has no course node - the course node is swapped for the activity node.
            if (!$courseformat->has_view_page()) {
                $targettype = navigation_node::TYPE_ACTIVITY;
            }

            while (!empty($coursenode) && ($coursenode->type != $targettype)) {
                $coursenode = $coursenode->parent;
            }
            // There is one very strange page in mod/feedback/view.php which thinks it is both site and course
            // context at the same time. That page is broken but we need to handle it (hence the SITEID).
            if ($coursenode && $coursenode->key != SITEID) {
                $this->add($flat);
                foreach ($coursenode->children as $child) {
                    if ($child->action) {
                        $flat = new flat_navigation_node($child, 0);
                        $this->add($flat);
                    }
                }
            }

            $this->build_flat_navigation_list($this, $this->page->navigation, true, get_string('site'));
        } else {
            $this->build_flat_navigation_list($this, $this->page->navigation, false, get_string('site'));
        }

        $admin = $PAGE->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN);
        if (!$admin) {
            // Try again - crazy nav tree!
            $admin = $PAGE->settingsnav->find('root', navigation_node::TYPE_SITE_ADMIN);
        }
        if ($admin) {
            $flat = new flat_navigation_node($admin, 0);
            $flat->set_showdivider(true, get_string('sitesettings'));
            $flat->key = 'sitesettings';
            $flat->icon = new pix_icon('t/preferences', '');
            $this->add($flat);
        }

        // Add-a-block in editing mode.
        if (isset($this->page->theme->addblockposition) &&
                $this->page->theme->addblockposition == BLOCK_ADDBLOCK_POSITION_FLATNAV &&
                $PAGE->user_is_editing() && $PAGE->user_can_edit_blocks()) {
            $url = new moodle_url($PAGE->url, ['bui_addblock' => '', 'sesskey' => sesskey()]);
            $addablock = navigation_node::create(get_string('addblock'), $url);
            $flat = new flat_navigation_node($addablock, 0);
            $flat->set_showdivider(true, get_string('blocksaddedit'));
            $flat->key = 'addblock';
            $flat->icon = new pix_icon('i/addblock', '');
            $this->add($flat);

            $addblockurl = "?{$url->get_query_string(false)}";

            $PAGE->requires->js_call_amd('core/addblockmodal', 'init',
                [$PAGE->pagetype, $PAGE->pagelayout, $addblockurl]);
        }
    }

    /**
     * Override the parent so we can set a label for this collection if it has not been set yet.
     *
     * @param navigation_node $node Node to add
     * @param string $beforekey If specified, adds before a node with this key,
     *   otherwise adds at end
     * @return navigation_node Added node
     */
    public function add(navigation_node $node, $beforekey=null) {
        $result = parent::add($node, $beforekey);
        // Extend the parent to get a name for the collection of nodes if required.
        if (empty($this->collectionlabel)) {
            if ($node instanceof flat_navigation_node) {
                $this->set_collectionlabel($node->get_collectionlabel());
            }
        }

        return $result;
    }

    /**
     * Walk the tree building up a list of all the flat navigation nodes.
     *
     * @deprecated since Moodle 4.0
     * @param flat_navigation $nodes List of the found flat navigation nodes.
     * @param boolean $showdivider Show a divider before the first node.
     * @param string $label A label for the collection of navigation links.
     */
    public function build_flat_navigation_list(flat_navigation $nodes, $pagenavigation, $showdivider = false, $label = '') {
        if ($pagenavigation->showinflatnavigation) {
            $indent = 0;
            if ($pagenavigation->type == navigation_node::TYPE_COURSE || $pagenavigation->key === navigation_node::COURSE_INDEX_PAGE) {
                $indent = 1;
            }
            $flat = new flat_navigation_node($pagenavigation, $indent);
            $flat->set_showdivider($showdivider, $label);
            $nodes->add($flat);
        }
        foreach ($pagenavigation->children as $child) {
            $this->build_flat_navigation_list($nodes, $child, false);
        }
    }
}
