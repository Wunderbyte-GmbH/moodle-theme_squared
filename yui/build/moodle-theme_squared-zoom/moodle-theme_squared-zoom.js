YUI.add('moodle-theme_squared-zoom', function (Y, NAME) {

/* zoom.js
 * copyright  2014 Bas Brands, www.basbrands.nl
 * authors    Bas Brands, David Scotson
 * license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  */

var onZoom = function() {
  var zoomin = Y.one('body').hasClass('zoomin');
  if (zoomin) {
    Y.one('body').removeClass('zoomin');
    Y.one('body').addClass('nozoom');
    M.util.set_user_preference('theme_squared_zoom', 'nozoom');
  } else {
    Y.one('body').removeClass('nozoom');
    Y.one('body').addClass('zoomin');
    M.util.set_user_preference('theme_squared_zoom', 'zoomin');
  }
};

//When the button with class .moodlezoom is clicked fire the onZoom function
M.theme_squared = M.theme_squared || {};
M.theme_squared.zoom =  {
  init: function() {
    Y.one('body').delegate('click', onZoom, '.moodlezoom');
  }
};

}, '@VERSION@', {"requires": ["node"]});
