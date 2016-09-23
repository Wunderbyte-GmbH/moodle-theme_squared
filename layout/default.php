<?php
// This file is part of The Bootstrap Moodle theme
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


$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$knownregionpre = $PAGE->blocks->is_known_region('side-pre');
$knownregionpost = $PAGE->blocks->is_known_region('side-post');

$regions = theme_squared_grid($hassidepre);
$PAGE->set_popup_notification_allowed(false);
if ($knownregionpre || $knownregionpost) {
    theme_squared_initialise_zoom($PAGE);
}
$setzoom = theme_squared_get_zoom();
$PAGE->requires->jquery();
$html = $PAGE->get_renderer('theme_squared', 'html');

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes($html->toplevel_category() . ' ' . $setzoom); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php echo $html->navigation_menu(); ?>

<?php echo $html->image_header(); ?>

<div id="page" class="container-fluid">
    <header id="page-header" class="clearfix">
        <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div>
    </header>

    <div id="page-content" class="row">
        <div id="region-main" class="<?php echo $regions['content']; ?>">
            <div class="inner">
                <div id="page-navbar" class="clearfix">
                    <button class="moodlezoom">
                        <i class="glyphicon glyphicon-resize-full tosmall"></i>
                        <i class="glyphicon glyphicon-resize-small tofull"></i>
                    </button>
                    <nav class="breadcrumb-nav" role="navigation" aria-label="breadcrumb"><?php echo $OUTPUT->navbar(); ?></nav>
                    <div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
                </div>

                <?php
                echo $OUTPUT->context_header();
                echo $OUTPUT->course_content_header();
                echo $OUTPUT->main_content();
                echo $OUTPUT->course_content_footer();
                ?>
                <div class="clearfix"></div>
            </div>
        </div>

        <?php
        if ($knownregionpre) {
            echo $OUTPUT->blocks('side-pre', $regions['pre']);
        }?>
        <?php
        if ($knownregionpost) {
            echo $OUTPUT->blocks('side-post', $regions['post']);
        }?>
    </div>

    <?php echo $html->footer(); ?>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>
</body>
</html>
