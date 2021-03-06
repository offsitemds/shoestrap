<?php

// Prevent Direct Access with homepage redirect
if ( !defined( 'DB_NAME' ) ) {
  header('Location: http://'.$_SERVER['SERVER_NAME'].'/');
}


/*
 * Gets the css path or url to the stylesheet
 * If $target = 'path', return the path
 * If $target = 'url', return the url
 *
 * If echo = true then print the path or url.
 */
function shoestrap_css( $target = 'path', $echo = false ) {
  $cssid = null;
  // If this is a multisite installation, append the blogid to the filename
  if ( is_multisite() ) {
    global $blog_id;
    if ( $blog_id > 1 )
      $cssid = '_id-' . $blog_id;
    else
      $cssid = null;
  }



  if ( $target == 'url' )
    $css_path = get_template_directory_uri() . '/assets/css/style' . $cssid. '.css';
  else
    $css_path = get_template_directory() . '/assets/css/style' . $cssid. '.css';

  if ( $echo )
    echo $css_path;
  else
    return $css_path;
}


/*
 * Admin notice if css or less files are writable
 */
function shoestrap_css_not_writeable($array){
  global $current_screen, $wp_filesystem;

  if ( $current_screen->parent_base == 'themes' ) {
    $filename = shoestrap_css();
    $url = shoestrap_css('url');
    
    if (!file_exists($filename)) {
  	  	if ( ! $wp_filesystem->put_contents( $filename, " ", FS_CHMOD_FILE) ) {
					$content = __( "The following file does not exist and must be so in order to utilise this theme. Please create this file.", "shoestrap");
    			$content .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$filename.'" target="_blank">'.$filename.'</a>';
					add_settings_error( 'shoestrap', 'create_file', $content, 'error' );  				    		
		    	settings_errors();	    		
	  	}
    }

    if (file_exists($filename) && !is_writable($filename)) {
    	$content = __( "The following file is not writable and must be so in order to utilise this theme. Please update the permissions.", "shoestrap");
    	$content .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$filename.'" target="_blank">'.$filename.'</a>';
	
			add_settings_error( 'shoestrap', 'create_file', $content, 'error' );  				    		
	    settings_errors();	
    }
  }
}
add_action( 'admin_notices', 'shoestrap_css_not_writeable');


/*
 * This function can be used to compile a less file to css using the lessphp compiler
 */
function shoestrap_phpless_compiler() {

  if ( !class_exists( 'lessc' ) )
    require_once locate_template( '/lib/less_compiler/lessc.inc.php' );

  $less = new lessc;

  // $less->setFormatter( "compressed" );

  $less->setImportDir( array(
    get_template_directory() . '/assets/less',
    get_template_directory() . '/assets/fonts',
  ) );
  $css = $less->compile( shoestrap_complete_less() );



  return $css;
}


function shoestrap_compile_css( $method = 'php' ) {
	global $wp_filesystem;
	
	// Initialize the Wordpress filesystem, no more using file_put_contents function
	if (empty($wp_filesystem)) {
		require_once(ABSPATH .'/wp-admin/includes/file.php');
		WP_Filesystem();
	}
  $content = '/********* Do not edit this file *********/

';
	
  if ( $method == 'php' ) {
    if ( get_option( 'shoestrap_activated' ) == 1 ) {
      $content .= shoestrap_phpless_compiler();
    	$file = shoestrap_css();
    	if (is_writeable($file)
    	|| (!file_exists($file) && is_writeable(dirname($file))) ) {
  	  		if ( ! $wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE) ) {
				return $css;
	  		}
    	} 
    }
  } 
  
}


/*
 * Write the CSS to file
 */
function shoestrap_makecss() {
  shoestrap_compile_css();
}


function shoestrap_process_font( $font ) {
  if ( isset( $font['style'] ) ) {
    $temp = explode( "-", $font['style'] );
    $font['weight'] = $temp[0];
    if ( isset( $temp[1] ) ) {
      $font['style'] = $temp[1];
    } else {
      $font['style'] = "inherit"; // Default style
    }
  } else {
    $font['weight'] = "inherit";
    $font['style'] = "inherit";
  }
  if ( isset($font['size']) )
    $font['size'] = filter_var( $font['size'], FILTER_SANITIZE_NUMBER_INT );
  if ( $font['weight'] == "" )
    $font['weight'] = "inherit";

  return $font;
}


/*
 * The content below is a copy of bootstrap's variables.less file.
 *
 * Some options are user-configurable and stored as theme mods.
 * We try to minimize the options and simplify the user environment.
 * In order to do that, we 'll have to provide a minimum amount of options
 * and calculate the rest based on the user's selections.
 *
 */
function shoestrap_variables_less() {

  $body_bg          = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_body_bg', true ) );
  $brand_primary    = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_primary', true ) );
  $brand_secondary  = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_secondary', true ) );
  $brand_success    = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_success', true ) );
  $brand_warning    = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_warning', true ) );
  $brand_danger     = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_danger', true ) );
  $brand_info       = '#' . str_replace( '#', '', shoestrap_getVariable( 'color_brand_info', true ) );

  $font_base              = shoestrap_process_font( shoestrap_getVariable( 'font_base', true ) );
  $font_navbar            = shoestrap_process_font( shoestrap_getVariable( 'font_navbar', true ) );
  $font_brand             = shoestrap_process_font( shoestrap_getVariable( 'font_brand', true ) );
  $font_jumbotron         = shoestrap_process_font( shoestrap_getVariable( 'font_jumbotron', true ) );
  $font_heading           = shoestrap_process_font( shoestrap_getVariable( 'font_heading', true ) );  

  if ( shoestrap_getVariable( 'font_heading_custom', true ) == 1 ) {

    $font_h1 = shoestrap_process_font( shoestrap_getVariable( 'font_h1', true ) );
    $font_h2 = shoestrap_process_font( shoestrap_getVariable( 'font_h2', true ) );
    $font_h3 = shoestrap_process_font( shoestrap_getVariable( 'font_h3', true ) );
    $font_h4 = shoestrap_process_font( shoestrap_getVariable( 'font_h4', true ) );
    $font_h5 = shoestrap_process_font( shoestrap_getVariable( 'font_h5', true ) );
    $font_h6 = shoestrap_process_font( shoestrap_getVariable( 'font_h6', true ) );

    $font_h1_face   = $font_h1['face'];
    $font_h1_size   = $font_h1['size'] . 'px';
    $font_h1_weight = $font_h1['weight'];
    $font_h1_style  = $font_h1['style'];

    $font_h2_face   = $font_h2['face'];
    $font_h2_size   = $font_h2['size'] . 'px';
    $font_h2_weight = $font_h2['weight'];
    $font_h2_style  = $font_h2['style'];

    $font_h3_face   = $font_h3['face'];
    $font_h3_size   = $font_h3['size'] . 'px';
    $font_h3_weight = $font_h3['weight'];
    $font_h3_style  = $font_h3['style'];

    $font_h4_face   = $font_h4['face'];
    $font_h4_size   = $font_h4['size'] . 'px';
    $font_h4_weight = $font_h4['weight'];
    $font_h4_style  = $font_h4['style'];

    $font_h5_face   = $font_h5['face'];
    $font_h5_size   = $font_h5['size'] . 'px';
    $font_h5_weight = $font_h5['weight'];
    $font_h5_style  = $font_h5['style'];

    $font_h6_face   = $font_h6['face'];
    $font_h6_size   = $font_h6['size'] . 'px';
    $font_h6_weight = $font_h6['weight'];
    $font_h6_style  = $font_h6['style'];

  } else {

    $font_h1_face   = '@font-family-base';
    $font_h1_size   = 'ceil(@font-size-base * 2.70)';
    $font_h1_weight = '@headings-font-weight';
    $font_h1_style  = 'inherit';

    $font_h2_face   = '@font-family-base';
    $font_h2_size   = 'ceil(@font-size-base * 2.25)';
    $font_h2_weight = '@headings-font-weight';
    $font_h2_style  = 'inherit';

    $font_h3_face   = '@font-family-base';
    $font_h3_size   = 'ceil(@font-size-base * 1.70)';
    $font_h3_weight = '@headings-font-weight';
    $font_h3_style  = 'inherit';

    $font_h4_face   = '@font-family-base';
    $font_h4_size   = 'ceil(@font-size-base * 1.25)';
    $font_h4_weight = '@headings-font-weight';
    $font_h4_style  = 'inherit';

    $font_h5_face   = '@font-family-base';
    $font_h5_size   = '@font-size-base';
    $font_h5_weight = '@headings-font-weight';
    $font_h5_style  = 'inherit';

    $font_h6_face   = '@font-family-base';
    $font_h6_size   = 'ceil(@font-size-base * 0.85)';
    $font_h6_weight = '@headings-font-weight';
    $font_h6_style  = 'inherit';

  }

  $text_color       = '#' . str_replace( '#', '', $font_base['color'] );
  $font_size_base   = $font_base['size'];
  $font_style_base  = $font_base['style'];
  $font_weight_base = $font_base['weight'];
  $sans_serif       = $font_base['face'];

  $border_radius    = filter_var( shoestrap_getVariable( 'general_border_radius', true ), FILTER_SANITIZE_NUMBER_INT );
  $padding_base     = intval( shoestrap_getVariable( 'padding_base', true ) );
  $navbar_color     = '#' . str_replace( '#', '', shoestrap_getVariable( 'navbar_color', true ) );
  $navbar_bg        = '#' . str_replace( '#', '', shoestrap_getVariable( 'navbar_bg', true ) );
  $jumbotron_bg     = '#' . str_replace( '#', '', shoestrap_getVariable( 'jumbotron_bg', true ) );

  $container_tablet         = filter_var( shoestrap_getVariable( 'container_tablet', true ), FILTER_SANITIZE_NUMBER_INT );
  $container_desktop        = filter_var( shoestrap_getVariable( 'container_desktop', true ), FILTER_SANITIZE_NUMBER_INT );
  $container_large_desktop  = filter_var( shoestrap_getVariable( 'container_large_desktop', true ), FILTER_SANITIZE_NUMBER_INT );
  $gutter                   = filter_var( shoestrap_getVariable( 'layout_gutter', true ), FILTER_SANITIZE_NUMBER_INT );

  $screen_small     = ( $container_tablet + $gutter );
  $screen_medium    = ( $container_desktop + $gutter );
  $screen_large     = ( $container_large_desktop + $gutter );

  $navbar_height    = filter_var( shoestrap_getVariable( 'navbar_height', true ), FILTER_SANITIZE_NUMBER_INT );
  $navbar_text_color       = '#' . str_replace( '#', '', $font_navbar['color'] );

  $brand_text_color       = '#' . str_replace( '#', '', $font_brand['color'] );
  $jumbotron_text_color   = '#' . str_replace( '#', '', $font_jumbotron['color'] );

  if ( shoestrap_getVariable( 'font_jumbotron_heading_custom', true ) == 1 ) {

    $font_jumbotron_headers = shoestrap_process_font( shoestrap_getVariable( 'font_jumbotron_headers', true ) );

    $font_jumbotron_headers_face   = $font_jumbotron_headers['face'];
    $font_jumbotron_headers_weight = $font_jumbotron_headers['weight'];
    $font_jumbotron_headers_style  = $font_jumbotron_headers['style'];
    $jumbotron_headers_text_color   = '#' . str_replace( '#', '', $font_jumbotron_headers['color'] );

  } else {

    $font_jumbotron_headers_face   = $font_jumbotron['face'];
    $font_jumbotron_headers_weight = $font_jumbotron['weight'];
    $font_jumbotron_headers_style  = $font_jumbotron['style'];
    $jumbotron_headers_text_color  = $jumbotron_text_color;
  }

  // Calculate the gray shadows based on the body background.
  // We basically create 2 "presets": light and dark.
  if ( shoestrap_get_brightness( $body_bg ) > 80 ) {
    $gray_darker  = 'lighten(#000, 13.5%)';
    $gray_dark    = 'lighten(#000, 20%)';
    $gray         = 'lighten(#000, 33.5%)';
    $gray_light   = 'lighten(#000, 60%)';
    $gray_lighter = 'lighten(#000, 93.5%)';
  } else {
    $gray_darker  = 'darken(#fff, 13.5%)';
    $gray_dark    = 'darken(#fff, 20%)';
    $gray         = 'darken(#fff, 33.5%)';
    $gray_light   = 'darken(#fff, 60%)';
    $gray_lighter = 'darken(#fff, 93.5%)';
  }

  if ( shoestrap_get_brightness( $brand_secondary ) > 50 )
    $link_hover_color = 'darken(@link-color, 15%)';
  else
    $link_hover_color = 'lighten(@link-color, 15%)';

  if ( shoestrap_get_brightness( $brand_secondary ) > 50 ) {
    $table_bg_accent      = 'darken(@body-bg, 2.5%)';
    $table_bg_hover       = 'darken(@body-bg, 4%)';
    $table_border_color   = 'darken(@body-bg, 13.35%)';
    $input_border         = 'darken(@body-bg, 20%)';
    $dropdown_divider_top = 'darken(@body-bg, 10.2%)';
  } else {
    $table_bg_accent      = 'lighten(@body-bg, 2.5%)';
    $table_bg_hover       = 'lighten(@body-bg, 4%)';
    $table_border_color   = 'lighten(@body-bg, 13.35%)';
    $input_border         = 'lighten(@body-bg, 20%)';
    $dropdown_divider_top = 'lighten(@body-bg, 10.2%)';
  }

  if ( shoestrap_get_brightness( $navbar_bg ) > 80 ) {
    $navbar_link_hover_color    = 'darken(@navbar-color, 26.5%)';
    $navbar_link_active_bg      = 'darken(@navbar-bg, 10%)';
    $navbar_link_disabled_color = 'darken(@navbar-bg, 6.5%)';
    $navbar_brand_hover_color   = 'darken(@navbar-link-color, 10%)';
  } else {
    $navbar_link_hover_color    = 'lighten(@navbar-color, 26.5%)';
    $navbar_link_active_bg      = 'lighten(@navbar-bg, 10%)';
    $navbar_link_disabled_color = 'lighten(@navbar-bg, 6.5%)';
    $navbar_brand_hover_color   = 'lighten(@navbar-link-color, 10%)';
  }


  $variables = '//
// Variables
// --------------------------------------------------


// Global values
// --------------------------------------------------


// Grays
// -------------------------

@gray-darker:            ' . $gray_darker . ';
@gray-dark:              ' . $gray_dark . ';
@gray:                   ' . $gray . ';
@gray-light:             ' . $gray_light . ';
@gray-lighter:           ' . $gray_lighter . ';

@ccc: mix(@gray-light, @gray-lighter);

// Brand colors
// -------------------------

@brand-primary:         ' . $brand_primary . ';
@brand-secondary:       ' . $brand_secondary . ';
@brand-success:         ' . $brand_success . ';
@brand-warning:         ' . $brand_warning . ';
@brand-danger:          ' . $brand_danger . ';
@brand-info:            ' . $brand_info . ';

// Scaffolding
// -------------------------

@body-bg:               ' . $body_bg . ';
@text-color:            ' . $text_color . ';

// Links
// -------------------------

@link-color:            ' . $brand_secondary . ';
@link-hover-color:      ' . $link_hover_color . ';

// Typography
// -------------------------

@font-family-sans-serif:  ' . $sans_serif . ';
@font-family-serif:       Georgia, "Times New Roman", Times, serif;
@font-family-monospace:   Monaco, Menlo, Consolas, "Courier New", monospace;
@font-family-base:        @font-family-sans-serif;

@font-size-base:          ' . $font_size_base . 'px;
@font-size-large:         ceil(@font-size-base * 1.25); // ~18px
@font-size-small:         ceil(@font-size-base * 0.85); // ~12px

@line-height-base:        1.428571429; // 20/14
@line-height-computed:    floor(@font-size-base * @line-height-base); // ~20px

@headings-font-family:    @font-family-base;
@headings-font-weight:    500;
@headings-line-height:    1.1;


// Components
// -------------------------
// Based on 14px font-size and 1.428 line-height (~20px to start)

@padding-base-vertical:          ' . $padding_base . 'px;
@padding-base-horizontal:        ' . round( $padding_base * 1.5 ) . 'px;

@padding-large-vertical:         ' . round( $padding_base * 1.75 ) . 'px;
@padding-large-horizontal:       ' . ( $padding_base * 2 ) . 'px;

@padding-small-vertical:         ' . round( $padding_base * 0.625 ) . 'px;
@padding-small-horizontal:       ' . round( $padding_base * 1.25 ) . 'px;

@line-height-large:              1.33;
@line-height-small:              1.5;

@border-radius-base:      ' . $border_radius . 'px;
@border-radius-large:     ceil(@border-radius-base * 1.5);
@border-radius-small:     floor(@border-radius-base * 0.75);

@component-active-bg:            @brand-primary;

@caret-width-base:               ceil(@font-size-small / 3 ); // ~4px
@caret-width-large:              ceil(@caret-width-base * (5/4) ); // ~5px

// Tables
// -------------------------

@table-cell-padding:                 ceil((@font-size-small * 2) / 3 ); // ~8px;
@table-condensed-cell-padding:       ceil(((@font-size-small / 3 ) * 5) / 4); // ~5px

@table-bg:                           transparent; // overall background-color
@table-bg-accent:                    ' . $table_bg_accent . '; // for striping
@table-bg-hover:                     ' . $table_bg_hover . '; // for hover
@table-bg-active:                    @table-bg-hover;

@table-border-color:                 ' . $table_border_color . '; // table and cell border


// Buttons
// -------------------------

@btn-font-weight:                bold;

@btn-default-color:              @gray-dark;
@btn-default-bg:                 @body-bg;
@btn-default-border:             @ccc;

@btn-primary-color:              @body-bg;
@btn-primary-bg:                 @brand-primary;
@btn-primary-border:             darken(@btn-primary-bg, 5%);

@btn-success-color:              @body-bg;
@btn-success-bg:                 @brand-success;
@btn-success-border:             darken(@btn-success-bg, 5%);

@btn-warning-color:              @body-bg;
@btn-warning-bg:                 @brand-warning;
@btn-warning-border:             darken(@btn-warning-bg, 5%);

@btn-danger-color:               @body-bg;
@btn-danger-bg:                  @brand-danger;
@btn-danger-border:              darken(@btn-danger-bg, 5%);

@btn-info-color:                 @body-bg;
@btn-info-bg:                    @brand-info;
@btn-info-border:                darken(@btn-info-bg, 5%);

@btn-link-disabled-color:        @gray-light;


// Forms
// -------------------------

@input-bg:                       @body-bg;
@input-bg-disabled:              @gray-lighter;

@input-border:                   @ccc;
@input-border-radius:            @border-radius-base;
@input-border-focus:             lighten(@brand-primary, 10%);

@input-color-placeholder:        @gray-light;

@input-height-base:              (@line-height-computed + (@padding-base-vertical * 2) + 2);
@input-height-large:             (floor(@font-size-large * @line-height-large) + (@padding-large-vertical * 2) + 2);
@input-height-small:             (floor(@font-size-small * @line-height-small) + (@padding-small-vertical * 2) + 2);

@legend-border-color:            @gray-lighter;

@input-group-addon-border-color: @input-border;


// Dropdowns
// -------------------------

@dropdown-bg:                    ' . $body_bg . ';
@dropdown-border:                rgba(0,0,0,.15);
@dropdown-fallback-border:       @input-border;
@dropdown-divider-bg:            @legend-border-color;

@dropdown-link-active-color:     ' . $body_bg . ';
@dropdown-link-active-bg:        @component-active-bg;

@dropdown-link-color:            @gray-dark;
@dropdown-link-hover-color:      ' . $body_bg . ';
@dropdown-link-hover-bg:         @dropdown-link-active-bg;

@dropdown-caret-color:           @gray-darker;

// COMPONENT VARIABLES
// --------------------------------------------------


// Z-index master list
// -------------------------
// Used for a birds eye view of components dependent on the z-axis
// Try to avoid customizing these :)

@zindex-dropdown:          1000;
@zindex-popover:           1010;
@zindex-tooltip:           1030;
@zindex-navbar-fixed:      1030;
@zindex-modal-background:  1040;
@zindex-modal:             1050;

// Media queries breakpoints
// --------------------------------------------------

// Extra small screen / phone
@screen-xsmall:              480px;
@screen-phone:               @screen-xsmall;

// Small screen / tablet
@screen-small:               ' . $screen_small . 'px;
@screen-tablet:              @screen-small;

// Medium screen / desktop
@screen-medium:              ' . $screen_medium . 'px;
@screen-desktop:             @screen-medium;

// Large screen / wide desktop
@screen-large:               ' . $screen_large . 'px;
@screen-large-desktop:       @screen-large;

// So media queries dont overlap when required, provide a maximum
@screen-small-max:           (@screen-medium - 1);
@screen-tablet-max:          (@screen-desktop - 1);
@screen-desktop-max:         (@screen-large-desktop - 1);


// Grid system
// --------------------------------------------------

// Number of columns in the grid system
@grid-columns:              12;
// Padding, to be divided by two and applied to the left and right of all columns
@grid-gutter-width:         ' . $gutter . 'px;
// Point at which the navbar stops collapsing
@grid-float-breakpoint:     @screen-tablet;


// Navbar
// -------------------------

// Basics of a navbar
@navbar-height:                    ' . $navbar_height . 'px;
@navbar-color:                     ' . $navbar_color . ';
@navbar-bg:                        ' . $navbar_bg . ';
@navbar-border-radius:             @border-radius-base;
@navbar-padding-horizontal:        floor(@grid-gutter-width / 2);  // ~15px
@navbar-padding-vertical:          ((@navbar-height - @line-height-computed) / 2);

// Navbar links
@navbar-link-color:                @navbar-color;
@navbar-link-hover-color:          ' . $navbar_link_hover_color . ';
@navbar-link-hover-bg:             transparent;
@navbar-link-active-color:         mix(@navbar-color, @navbar-link-hover-color, 50%);
@navbar-link-active-bg:            ' . $navbar_link_active_bg . ';
@navbar-link-disabled-color:       ' . $navbar_link_disabled_color . ';
@navbar-link-disabled-bg:          transparent;

// Navbar brand label
@navbar-brand-color:               @navbar-link-color;
@navbar-brand-hover-color:         ' . $navbar_brand_hover_color . ';
@navbar-brand-hover-bg:            transparent;

// Navbar toggle
@navbar-toggle-hover-bg:           @table-border-color;
@navbar-toggle-icon-bar-bg:        @ccc;
@navbar-toggle-border-color:       @table-border-color;


// Inverted navbar
//
// Reset inverted navbar basics
@navbar-inverse-color:                      @gray-light;
@navbar-inverse-bg:                         #222;

// Inverted navbar links
@navbar-inverse-link-color:                 @gray-light;
@navbar-inverse-link-hover-color:           @body-bg;
@navbar-inverse-link-hover-bg:              transparent;
@navbar-inverse-link-active-color:          @navbar-inverse-link-hover-color;
@navbar-inverse-link-active-bg:             darken(@navbar-inverse-bg, 10%);
@navbar-inverse-link-disabled-color:        #444;
@navbar-inverse-link-disabled-bg:           transparent;

// Inverted navbar brand label
@navbar-inverse-brand-color:                @navbar-inverse-link-color;
@navbar-inverse-brand-hover-color:          @body-bg;
@navbar-inverse-brand-hover-bg:             transparent;

// Inverted navbar search
// Normal navbar needs no special styles or vars
@navbar-inverse-search-bg:                  lighten(@navbar-inverse-bg, 25%);
@navbar-inverse-search-bg-focus:            @body-bg;
@navbar-inverse-search-border:              @navbar-inverse-bg;
@navbar-inverse-search-placeholder-color:   @ccc;

// Inverted navbar toggle
@navbar-inverse-toggle-hover-bg:            #333;
@navbar-inverse-toggle-icon-bar-bg:         @body-bg;
@navbar-inverse-toggle-border-color:        #333;


// Navs
// -------------------------

@nav-link-hover-bg:                         @gray-lighter;

@nav-disabled-link-color:                   @gray-light;
@nav-disabled-link-hover-color:             @gray-light;

@nav-open-link-hover-color:                 @body-bg;
@nav-open-caret-border-color:               @body-bg;

// Tabs
@nav-tabs-border-color:                     @table-border-color;

@nav-tabs-link-hover-border-color:          @gray-lighter;

@nav-tabs-active-link-hover-bg:             @body-bg;
@nav-tabs-active-link-hover-color:          @gray;
@nav-tabs-active-link-hover-border-color:   @table-border-color;

@nav-tabs-justified-link-border-color:            @table-border-color;
@nav-tabs-justified-active-link-border-color:     @body-bg;

// Pills
@nav-pills-active-link-hover-bg:            @component-active-bg;
@nav-pills-active-link-hover-color:         @body-bg;


// Pagination
// -------------------------

@pagination-bg:                        ' . $body_bg . ';
@pagination-border:                    ' . $table_border_color . ';
@pagination-active-bg:                 ' . $table_bg_hover . ';
@pagination-active-color:              @gray-light;
@pagination-disabled-color:            @gray-light;

// Pager
// -------------------------

@pager-border-radius:                  @navbar-padding-horizontal;
@pager-disabled-color:                 @gray-light;


// Jumbotron
// -------------------------

@jumbotron-bg:                   ' . $jumbotron_bg . ';


// Form states and alerts
// -------------------------

@state-warning-text:             #c09853;
@state-warning-bg:               #fcf8e3;
@state-warning-border:           darken(spin(@state-warning-bg, -10), 3%);

@state-danger-text:              #b94a48;
@state-danger-bg:                #f2dede;
@state-danger-border:            darken(spin(@state-danger-bg, -10), 3%);

@state-success-text:             #468847;
@state-success-bg:               #dff0d8;
@state-success-border:           darken(spin(@state-success-bg, -10), 5%);

@state-info-text:                #3a87ad;
@state-info-bg:                  #d9edf7;
@state-info-border:              darken(spin(@state-info-bg, -10), 7%);


// Tooltips
// -------------------------
@tooltip-max-width:           200px;
@tooltip-color:               ' . $body_bg . ';
@tooltip-bg:                  darken(@gray-darker, 15%);

@tooltip-arrow-width:         @padding-small-vertical;
@tooltip-arrow-color:         @tooltip-bg;


// Popovers
// -------------------------
@popover-bg:                          @body-bg;
@popover-max-width:                   276px;
@popover-border-color:                rgba(0,0,0,.2);
@popover-fallback-border-color:       @ccc;

@popover-title-bg:                    darken(@popover-bg, 3%);

@popover-arrow-width:                 (@tooltip-arrow-width * 2);
@popover-arrow-color:                 @body-bg;

@popover-arrow-outer-width:           (@popover-arrow-width + 1);
@popover-arrow-outer-color:           rgba(0,0,0,.25);
@popover-arrow-outer-fallback-color:  @gray-light;


// Labels
// -------------------------

@label-default-bg:            @gray-light;
@label-success-bg:            @brand-success;
@label-info-bg:               @brand-info;
@label-warning-bg:            @brand-warning;
@label-danger-bg:             @brand-danger;

@label-color:                 @body-bg;
@label-link-hover-color:      @body-bg;


// Modals
// -------------------------
@modal-inner-padding:         @line-height-computed;

@modal-title-padding:         ceil(@modal-inner-padding * (4/3)); // ~15px
@modal-title-line-height:     @line-height-base;

@modal-content-bg:                             @body-bg;
@modal-content-border-color:                   rgba(0,0,0,.2);
@modal-content-fallback-border-color:          @gray-light;

@modal-backdrop-bg:           darken(@gray-darker, 15%);
@modal-header-border-color:   lighten(@gray-lighter, 12%);
@modal-footer-border-color:   @modal-header-border-color;


// Alerts
// -------------------------
@alert-padding:               @modal-title-padding;
@alert-border-radius:         @border-radius-base;
@alert-link-font-weight:      bold;

@alert-bg:                    @state-warning-bg;
@alert-text:                  @state-warning-text;
@alert-border:                @state-warning-border;

@alert-success-bg:            @state-success-bg;
@alert-success-text:          @state-success-text;
@alert-success-border:        @state-success-border;

@alert-danger-bg:             @state-danger-bg;
@alert-danger-text:           @state-danger-text;
@alert-danger-border:         @state-danger-border;

@alert-info-bg:               @state-info-bg;
@alert-info-text:             @state-info-text;
@alert-info-border:           @state-info-border;


// Progress bars
// -------------------------
@progress-bg:                 ' . $table_bg_hover . ';
@progress-bar-color:          ' . $body_bg . ';

@progress-bar-bg:             @brand-primary;
@progress-bar-success-bg:     @brand-success;
@progress-bar-warning-bg:     @brand-warning;
@progress-bar-danger-bg:      @brand-danger;
@progress-bar-info-bg:        @brand-info;


// List group
// -------------------------
@list-group-bg:               ' . $body_bg . ';
@list-group-border:           ' . $table_border_color . ';
@list-group-border-radius:    @border-radius-base;

@list-group-hover-bg:         ' . $table_bg_hover . ';
@list-group-active-color:     ' . $body_bg . ';
@list-group-active-bg:        @component-active-bg;
@list-group-active-border:    @list-group-active-bg;

@list-group-link-color:          @gray;
@list-group-link-heading-color:  @gray-dark;


// Panels
// -------------------------
@panel-bg:                    ' . $body_bg . ';
@panel-border:                @list-group-border;
@panel-border-radius:         @border-radius-base;
@panel-heading-bg:            @list-group-hover-bg;
@panel-footer-bg:             @list-group-hover-bg;

@panel-primary-text:          ' . $body_bg . ';
@panel-primary-border:        @brand-primary;
@panel-primary-heading-bg:    @brand-primary;

@panel-success-text:          @state-success-text;
@panel-success-border:        @state-success-border;
@panel-success-heading-bg:    @state-success-bg;

@panel-warning-text:          @state-warning-text;
@panel-warning-border:        @state-warning-border;
@panel-warning-heading-bg:    @state-warning-bg;

@panel-danger-text:           @state-danger-text;
@panel-danger-border:         @state-danger-border;
@panel-danger-heading-bg:     @state-danger-bg;

@panel-info-text:             @state-info-text;
@panel-info-border:           @state-info-border;
@panel-info-heading-bg:       @state-info-bg;


// Thumbnails
// -------------------------
@thumbnail-caption-color:     @text-color;
@thumbnail-bg:                @body-bg;
@thumbnail-border:            @list-group-border;
@thumbnail-border-radius:     @border-radius-base;


// Wells
// -------------------------
@well-bg:                     @table-bg-hover;


// Accordion
// -------------------------
@accordion-border-color:      @legend-border-color;


// Badges
// -------------------------
@badge-color:                 @body-bg;
@badge-link-hover-color:      @body-bg;
@badge-bg:                    @gray-light;

@badge-active-color:          @link-color;
@badge-active-bg:             @body-bg;

@badge-font-weight:           bold;
@badge-line-height:           1;
@badge-border-radius:         10px;


// Breadcrumbs
// -------------------------
@breadcrumb-bg:               @table-bg-hover;
@breadcrumb-color:            @ccc;
@breadcrumb-active-color:     @gray-light;


// Carousel
// ------------------------

@carousel-text-shadow:                        0 1px 2px rgba(0,0,0,.6);

@carousel-control-color:                      @body-bg;
@carousel-control-width:                      15%;
@carousel-control-opacity:                    .5;
@carousel-control-font-size:                  @line-height-computed;

@carousel-indicator-active-bg:                @body-bg;
@carousel-indicator-border-color:             @body-bg;

@carousel-caption-color:                      @body-bg;


// Close
// ------------------------
@close-color:                 darken(@gray-darker, 15%);
@close-font-weight:           bold;
@close-text-shadow:           0 1px 0 @body-bg;


// Code
// ------------------------
@code-color:                  #c7254e;
@code-bg:                     #f9f2f4;

@pre-bg:                      #f5f5f5;
@pre-border-color:            @ccc;

// Type
// ------------------------
@text-muted:                  @gray-light;
@abbr-border-color:           @gray-light;
@headings-small-color:        @gray-light;
@blockquote-small-color:      @gray-light;
@blockquote-border-color:     @gray-lighter;
@page-header-border-color:    @gray-lighter;

// Miscellaneous
// -------------------------

// Hr border color
@hr-border:                   @gray-lighter;

// Horizontal forms & lists
@component-offset-horizontal: 180px;


// Container sizes
// --------------------------------------------------

// Small screen / tablet
@container-tablet:          ' . $container_tablet . 'px;

// Medium screen / desktop
@container-desktop:         ' . $container_desktop . 'px;

// Large screen / wide desktop
@container-large-desktop:   ' . $container_large_desktop . 'px;




// Shoestrap-specific variables
// --------------------------------------------------

@navbar-font-size:        ' . $font_navbar['size'] . 'px;
@navbar-font-weight:      ' . $font_navbar['weight'] . ';
@navbar-font-style:       ' . $font_navbar['style'] . ';
@navbar-font-family:      ' . $font_navbar['face'] . ';
@navbar-font-color:       ' . $navbar_text_color . ';

@brand-font-size:         ' . $font_brand['size'] . 'px;
@brand-font-weight:       ' . $font_brand['weight'] . ';
@brand-font-style:        ' . $font_brand['style'] . ';
@brand-font-family:       ' . $font_brand['face'] . ';
@brand-font-color:        ' . $brand_text_color . ';

@jumbotron-font-size:         ' . $font_jumbotron['size'] . 'px;
@jumbotron-font-weight:       ' . $font_jumbotron['weight'] . ';
@jumbotron-font-style:        ' . $font_jumbotron['style'] . ';
@jumbotron-font-family:       ' . $font_jumbotron['face'] . ';
@jumbotron-font-color:        ' . $jumbotron_text_color . ';

@jumbotron-headers-font-weight:       ' . $font_jumbotron_headers_weight . ';
@jumbotron-headers-font-style:        ' . $font_jumbotron_headers_style . ';
@jumbotron-headers-font-family:       ' . $font_jumbotron_headers_face . ';
@jumbotron-headers-font-color:        ' . $jumbotron_headers_text_color . ';

// H1
@heading-h1-face:         ' . $font_h1_face . ';
@heading-h1-size:         ' . $font_h1_size . ';
@heading-h1-weight:       ' . $font_h1_weight . ';
@heading-h1-style:        ' . $font_h1_style . ';

// H2
@heading-h2-face:         ' . $font_h2_face . ';
@heading-h2-size:         ' . $font_h2_size . ';
@heading-h2-weight:       ' . $font_h2_weight . ';
@heading-h2-style:        ' . $font_h2_style . ';

// H3
@heading-h3-face:         ' . $font_h3_face . ';
@heading-h3-size:         ' . $font_h3_size . ';
@heading-h3-weight:       ' . $font_h3_weight . ';
@heading-h3-style:        ' . $font_h3_style . ';

// H4
@heading-h4-face:         ' . $font_h4_face . ';
@heading-h4-size:         ' . $font_h4_size . ';
@heading-h4-weight:       ' . $font_h4_weight . ';
@heading-h4-style:        ' . $font_h4_style . ';

// H5
@heading-h5-face:         ' . $font_h5_face . ';
@heading-h5-size:         ' . $font_h5_size . ';
@heading-h5-weight:       ' . $font_h5_weight . ';
@heading-h5-style:        ' . $font_h5_style . ';

// H6
@heading-h6-face:         ' . $font_h6_face . ';
@heading-h6-size:         ' . $font_h6_size . ';
@heading-h6-weight:       ' . $font_h6_weight . ';
@heading-h6-style:        ' . $font_h6_style . ';

@navbar-margin-top:       ' . shoestrap_getVariable( 'navbar_margin_top' ) . 'px;

';

  return $variables;
}


/**
	Brings all the LESS files that need to be compiled together.
**/
function shoestrap_complete_less( $url = false ) {
  if ( $url == true ) {
    $bootstrap    = get_template_directory_uri().'/assets/less/';
    $fonts        = get_template_directory_uri().'/assets/fonts/';
  } else {
    $bootstrap    = NULL;
    $fonts        = NULL;
  }
  $bootstrap_less = shoestrap_variables_less() . '
/*!
 * Bootstrap v3.0.0
 *
 * Copyright 2013 Twitter, Inc
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Designed and built with all the love in the world by @mdo and @fat.
 */

// Core variables and mixins
// @import "'.$bootstrap.'variables";
@import "'.$bootstrap.'mixins";

// Reset
@import "'.$bootstrap.'normalize";
@import "'.$bootstrap.'print";

// Core CSS
@import "'.$bootstrap.'scaffolding";
@import "'.$bootstrap.'type";
@import "'.$bootstrap.'code";
@import "'.$bootstrap.'grid";

@import "'.$bootstrap.'tables";
@import "'.$bootstrap.'forms";
@import "'.$bootstrap.'buttons";

// Components: common
@import "'.$bootstrap.'component-animations";
@import "'.$bootstrap.'input-groups"; 
@import "'.$fonts.'elusive-webfont";
@import "'.$bootstrap.'dropdowns";
@import "'.$bootstrap.'list-group";
@import "'.$bootstrap.'panels";
@import "'.$bootstrap.'wells";
@import "'.$bootstrap.'close";

// Components: Nav
@import "'.$bootstrap.'navs";
@import "'.$bootstrap.'navbar";
@import "'.$bootstrap.'button-groups";
@import "'.$bootstrap.'breadcrumbs";
@import "'.$bootstrap.'pagination";
@import "'.$bootstrap.'pager";

// Components: Popovers
@import "'.$bootstrap.'modals";
@import "'.$bootstrap.'tooltip";
@import "'.$bootstrap.'popovers";

// Components: Misc
@import "'.$bootstrap.'alerts";
@import "'.$bootstrap.'thumbnails";
@import "'.$bootstrap.'media";
@import "'.$bootstrap.'labels";
@import "'.$bootstrap.'badges";
@import "'.$bootstrap.'progress-bars";
@import "'.$bootstrap.'accordion";
@import "'.$bootstrap.'carousel";
@import "'.$bootstrap.'jumbotron";

// Utility classes
@import "'.$bootstrap.'utilities"; // Has to be last to override when necessary
@import "'.$bootstrap.'responsive-utilities";

// Custom Shoestrap less-css
//@import "'.$bootstrap.'retina";
// NEED TO FIX THIS TO COMPILE!
@import "'.$bootstrap.'app";
';

if (is_writable(get_template_directory() . '/assets/less/custom.less')) {
    $bootstrap_less .= '
  // Custom LESS file for developers
  @import "'.$bootstrap.'custom";';
}


if ($url == true) {
  $bootstrap_less .="@elusiveWebfontPath: '".$fonts."'; // Elusive webfonts path;
";
}

  return $bootstrap_less;
}
