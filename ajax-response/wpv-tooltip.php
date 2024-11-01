<?php
$_tooltip_id = $_POST["wpv-tooltip"];
if (function_exists("show_$_tooltip_id") === FALSE) {
    echo "Not implemented";
}
else {
    eval("show_$_tooltip_id();");
}
?>

<?php function show_display_option_table_location() { ?>
    <p>
    <strong>Table Location</strong> describes where the thumbnail table is displayed, relative to the post content.
    </p>
<?php } ?>

<?php function show_display_option_table_width() { ?>
    <p>
    <strong>Table Width</strong> is the width of the thumbnail table.
    </p>
    <p>
    If the unit is set to %, the max value is 100.  If larger value is entered, it is automatically set to 100.
    </p>
<?php } ?>

<?php function show_display_option_table_border() { ?>
    <p>
    <strong>Border</strong> is the border of the thumbnail table.  Here you can specify width, style and color of the border.
    </p>
<?php } ?>

<?php function show_display_option_table_margin() { ?>
    <p>
    <strong>Margin</strong> allows you to specify the margin between the linked table and the rest of the content in a post or page.
    </p>
<?php } ?>

<?php function show_display_option_column_count() { ?>
    <p>
    <strong>Column Count</strong> is the number of column the table will contain when the linked files are published.  This value should be modified depending on space available, thumbnail size, etc.
    </p>
<?php } ?>

<?php function show_display_option_display_thumbnail() { ?>
    <p>
    <strong>Thumbnail Display Option</strong> describes how thumbnails are displayed, respect to text data.  This may be irrelevant, if you choose not to display any text in <strong>Text Display Option</strong>.
    </p>
    <p>
    <ul>
    <li><strong>Top</strong>: Displays thumbnail above text.</li>
    <li><strong>Bottom</strong>: Displays thumbnail below text.</li>
    <li><strong>Left</strong>: Displays thumbnail at left of text.</li>
    <li><strong>Right</strong>: Displays thumbnail at right of text.</li>
    <li><strong>Stagger</strong>: Thumbnail and text staggers.  If the first row has the thumbnail to the left of text, the thumbnail would be to the right of text on the next row, and so on.</li>
    <li><strong>None</strong>: No thumbnails are displayed.  Only text.</li>
    </ul>
    </p>
<?php } ?>

<?php function show_display_option_text_display() { ?>
    <p>
    <strong>Text Display Option</strong> describes what text data should be displayed.  You can display, file name, comment (if you specified any), both, or none.
    </p>
<?php } ?>

<?php function show_display_option_horizontal_align() { ?>
    <p>
    <strong>Horizontal Alignment</strong> is the horizontal alignments of both image and text within their respective cells.
    </p>
<?php } ?>

<?php function show_display_option_vertical_align() { ?>
    <p>
    <strong>Vertical Alignment</strong> is the vertical alignments of both image and text within their respective cells.
    </p>
<?php } ?>

<?php function show_display_option_target_thumbnail_size() { ?>
    <p>
    <strong>Target Thumbnail Size</strong> describes the width and the height of target thumbnail size in pixels.  The system will create a resized thumbnails to fit in a target size x target size square.
    </p>
    <p>
    For example, if your original image size is 1000 x 500, and you set the Target Thumbnail Size to 100px, the thumbnail's demension would be 100 x 50.
    </p>
<?php } ?>

<?php function show_display_option_target_image_size() { ?>
    <p>
    <strong>Target Image Size</strong> describes the width and the height of target image size in pixels.  The system will create a resized image to fit in a target size x target size square when it's being displayed.
    </p>
    <p>
    For example, if your original image size is 1000 x 500, and you set the Target Image Size to 500px, the image's demension would be 500 x 250.
    </p>
    <p>
    This operation has no effect to the original image.  Also, if a file is not an image file, this option does not do anything.
    </p>
<?php } ?>

<?php function show_display_option_border_color() { ?>
    <p>
    <strong>Border Color</strong> is the border color of grid. 
    </p>
<?php } ?>

<?php function show_display_option_cell_background_color() { ?>
    <p>
    <strong>Cell Background Color</strong> is the background color of all cells that contain thumbnail images and texts.  <strong>Hover</strong> describes the background color when the mouse cursor moves over a cell.
    </p>
<?php } ?>

<?php function show_display_option_cell_border() { ?>
    <p>
    <strong>Cell Border</strong> defines the color, hover color (color when mouse moves over a cell), and border width of individual cell.
    </p>
<?php } ?>

<?php function show_display_option_file_name_text() { ?>
    <p>
    <strong>File Name Text</strong> describes how file name text should be displayed, if you chose to display it in <strong>Text Display Option</strong>.  Otherwise, this option has no effect.
    </p>
<?php } ?>

<?php function show_display_option_comment_text() { ?>
    <p>
    <strong>Comment Text</strong> describes how comment text should be displayed, if you chose to display it in <strong>Text Display Option</strong>.  Otherwise, this option has no effect.
    </p>
<?php } ?>

<?php function show_display_option_image_display_background_color() { ?>
    <p>
    <strong>Background Color</strong> is the background color of the image display window.
    </p>
<?php } ?>

<?php function show_display_option_image_display_border_color() { ?>
    <p>
    <strong>Border Color</strong> is the border color of the image display window.
    </p>
<?php } ?>

<?php function show_display_option_image_display_name_font() { ?>
    <p>
    <strong>File Name Font</strong> is where you can set the font used to display the file name in the image display window.
    </p>
    <p>
    If it is configured not to display the file name, this option has no effect.
    </p>
<?php } ?>

<?php function show_display_option_display_status() { ?>
    <p>
    <strong>Display Status</strong> describes the state of current "gallery."  If it's set to "Published," linked files are available to public.
    </p>
    <p>
    Note that this is independent of status for post or page where files are linked to.  For files to be truly visible by general public, both post/page and linked files must be published.
    </p>
<?php } ?>

<?php function show_option_file_path() { ?>
    <p>
    <strong>File Path</strong> is the location where WP Vault stores all the uploaded files.  This directory must be writable by web server.
    </p>
<?php } ?>

<?php function show_option_target_thumbnail_size() { ?>
    <p>
    <strong>Default Target Thumbnail Size</strong> describes the default width and height of target thumbnail size in pixels.  The system will create a resized thumbnails to fit in a target size x target 
	size square.  This is the value that is used initially when you first attach files to a post.
    </p>
<?php } ?>

<?php function show_option_target_image_size() { ?>
    <p>
    <strong>Default Target Image Size</strong> describes the default width and height of target image size in pixels.  The system will create a resized image to fit in a target size x target size square when it's being displayed.
	This is the value that is used initially when you first attach files to a post.
    </p>
    <p>
    This operation has no effect to the original image.  Also, if a file is not an image file, this option does not do anything.
    </p>
    <p>
	This value is also used as the target size for images within WP Vault admin screens, such as File Browser.
    </p>
<?php } ?>

<?php function show_option_role_access() { ?>
    <p>
    <strong>Role Access</strong> is the minimum role access level to thave access to WP Vault.
    </p>
    <p>
	System Administrators always have access to all files online, regardless of who owns it.
    </p>
    <p>
	Editors, if given access, can edit and delete own files, and to attach any files to posts, regardless of the ownership.
    </p>
    <p>
	With any other access to WP Vault, they can only access their own files.
    </p>
<?php } ?>
