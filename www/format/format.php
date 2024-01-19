<?php

require_once(Config::ROOT."common/db/user.php");
require_once(Config::ROOT."common/user.php");
require_once(Config::ROOT."common/rating.php");
require_once(Config::ROOT."www/url.php");
require_once(Config::ROOT."www/utilities.php");

// Format an array of xml attributes.
// Return '' or 'k1="v1" k2="v2"'.
// Escapes values, checks keys.
function format_attribs($attribs = array())
{
    log_assert(is_array($attribs), 'You must pass an array');

    $result = "";
    foreach ($attribs as $k => $v) {
        if (is_null($v))
            continue;

        log_assert(preg_match("/[a-z][a-z_0-9]*/", $k), "Invalid attrib '$k'");
        if ($result == "") {
            $result .= "$k=\"".html_escape($v)."\"";
        } else {
            $result .= " $k=\"".html_escape($v)."\"";
        }
    }

    return $result;
}

// Format an open html tag:
// <tag k1="v1" k2="v2" .. >
// You have to manually close the tag somehow.
// You can use format_tag with no content for an empty <...> tag.
function format_open_tag($tag, $attribs = array())
{
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    return "<$tag " . format_attribs($attribs) . ">";
}

// Format a html tag.
// Tag is a tag name(img, th, etc).
//
// Attrib values are escaped. Content is escaped by default.
// Tag and attrib keys are checked.
function format_tag($tag, $content = null, $attribs = array(), $escape = true) {
    log_assert(is_array($attribs), 'attribs is not an array');
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    if (is_null($content)) {
        return "<$tag ".format_attribs($attribs).">";
    } else {
        if ($escape) {
            $content = html_escape($content);
        }
        return "<$tag ".format_attribs($attribs).">$content</$tag>";
    }
}

// Build a simple href
// By default escapes url & content
//
// You can set escape_content to false.
function format_link($url, $content, $escape = true, $attr = array()) {
    log_assert(is_array($attr), '$attr is not an array');
    if ($url) {
        $attr['href'] = $url;
    }
    return format_tag("a", $content, $attr, $escape);
}

// Build a link which posts data to a page
function format_post_link($url, $content, $post_data = array(), $escape = true, $attr = array(), $accesskey = null) {
    log_assert(is_array($attr), '$attr is not an array');
    log_assert(is_array($post_data), '$post_data is not an array');

    $link_url = "javascript:PostData(" . json_encode($url) . ", " . json_encode($post_data) . ")";

    if (is_null($accesskey)) {
        $link = format_link($link_url, $content, $escape, $attr);
    } else {
        $link = format_link_access($link_url, $content, $accesskey, $attr);
    }

    // Display a little "check" button beside the link if
    // javascript is disabled, by using a form with hidden fields.
    $form_content = '<input type="submit" style="margin: 0; padding: 0" value="&#10003;">';
    foreach ($post_data as $key => $value) {
        $form_content .= '<input type="hidden" name="' . html_escape($key) . '" value="' . html_escape($value) . '">';
    }
    $form_attr = array("class" => "inline_form",
                       "method" => "post",
                       "action" => $url,
                      );

    return $link . "<noscript>" . format_tag("form", $form_content, $form_attr, false) . "</noscript>";
}

// Highlight an access key in a string, by surrounding the first occurence
// of the $key with <span class="access-key"></span>
// Case insensitive, nothing happens if $key is not found.
// FIXME: Improve this logic.
function format_highlight_access_key($string, $key) {
    if (($pos = stripos($string, $key)) !== false) {
        return substr_replace($string,
                '<span class="access-key">'.$string[$pos].'</span>', $pos, 1);
    } else {
        return $string;
    }
}

// Format a link with an access key.
// Html content not supported because of format_highlight_access_key.
function format_link_access($url, $content, $key, $attr = array()) {
    $attr['accesskey'] = $key;
    $content = format_highlight_access_key(html_escape($content), $key);
    return format_link($url, $content, false, $attr);
}

// Format a tiny user link, with a 16x16 avatar.
function format_user_tiny(string $username): string {
  $user = User::get_by_username($username);
  Smart::assign([
    'user' => $user,
    'showRating' => true,
  ]);
  return Smart::fetch('bits/userTiny.tpl');
}

// Format a normal user link, with a 32x32 avatar.
function format_user_normal(string $username): string {
  $user = User::get_by_username($username);
  Smart::assign('user', $user);
  return Smart::fetch('bits/userNormal.tpl');
}

// Return rating group and colour based on user's sclaed rating scale.
// Rating groups (from highest to lowest ranking): 1, 2, 3, 4, 0
// NOTE: It outputs 0 when user is not rated
function rating_group($rating, $is_admin = false) {
    if ($is_admin) {
        // all mighty admin - black
        return array("group" => 5, "colour" => "#000000");
    }
    if (!$rating) {
        // user unrated - white
        return array("group" => 0, "colour" => "#ffffff");
    }
    if ($rating < 540) {
        // green
        return array("group" => 4, "colour" => "#00a900");
    }
    else if ($rating < 600) {
        // blue
        return array("group" => 3, "colour" => "#0000ff");
    }
    else if ($rating < 700) {
        // yellow
        return array("group" => 2, "colour" => "#ddcc00");
    }
    else {
        // red
        return array("group" => 1, "colour" => "#ee0000");
    }
}

// Formats user rating badge. Rating badges are displayed before username
// and indicate the user's rating.
function format_user_ratingbadge($username, $rating) {
    if ($rating) {
        $is_admin = user_is_admin(user_get_by_username($username));
        $rating = rating_scale($rating);
        $rating_group = rating_group($rating, $is_admin);
        $class = $rating_group["group"];
        $att = array(
            'title' => 'Rating '.html_escape($username).': '.$rating,
            'class' => 'rating-badge-'.$class,
        );
        return format_link(url_user_rating($username), '&bull;', false, $att);
    }
    else {
        // un-rated users have no badge
        return '';
    }
}

// Format a date for display.
// Can take *both* unix timestamps and utc strings(db_date stuff).
//
// FIXME: user timezone, user format, etc.
// global identityUser;
//
// HTML safe(don't pass through html_escape.)
function format_date($date, $format = null) {
    if (is_db_date($date)) {
        $timestamp = db_date_parse($date);
    } elseif (is_whole_number($date)) {
        $timestamp = $date;
    } elseif (is_null($date)) {
        $timestamp = time();
    } else {
        log_error("Invalid date argument");
    }

    if (is_null($format)) {
        $format = Config::DATE_FORMAT;
    }

    $timeZone = new DateTimeZone(Config::TIMEZONE);
    $dt = new DateTime('@' . $timestamp);
    $dt->setTimeZone($timeZone);
    $res = IntlDateFormatter::formatObject($dt, $format, 'ro_RO.utf8');
    return $res;
}
