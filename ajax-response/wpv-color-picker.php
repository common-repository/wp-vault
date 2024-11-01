<table id="wpv-color-picker" colspan="0" border="0" cellspacing="0">
    <tr class="header">
    <td class="title">Color Picker</td>
    <td class="close-button"><a class="close-button" href="javascript:void(0)" onclick="WpvColorPicker.hidePicker()" title="Close">&nbsp;X&nbsp;</a></td>
    </tr>
    <tr>
    <td colspan="2">

        <table cellspacing="0" cellpadding="0" border="0" style="cursor: default; background-color: #000000;">
        <tr>
        <?php
        for ($gray = -1; $gray < 256; $gray += 4) {
            $color = wpv_get_color($gray, $gray, $gray);
            ?>
            <td><div onclick="WpvColorPicker.pickColor('#<?php echo $color; ?>', '<?php echo $_POST["target_id"]; ?>')" onmouseover="WpvColorPicker.showColor('#<?php echo $color; ?>')" style="width: 5px; height: 15px; background-color: #<?php echo $color; ?>"></div></td>
            <?php
        }
        ?>
        </tr>
        <?php
        for ($blue = -1; $blue < 256; $blue += 32) {
        ?>
        <tr>
            <?php
            for ($green = -1; $green < 256; $green += 32) {
                for ($red = -1; $red < 256; $red += 32) {
                    $color = wpv_get_color($red, $green, $blue);
                ?>
                    <td><div onclick="WpvColorPicker.pickColor('#<?php echo $color; ?>', '<?php echo $_POST["target_id"]; ?>')" onmouseover="WpvColorPicker.showColor('#<?php echo $color; ?>')" style="width: 5px; height: 15px; background-color: #<?php echo $color; ?>"></div></td>
                <?php
                }
            }
            ?>
        </tr>
        <?php
        }
        ?>
        </table>
        <div id="color-text" style="float: left; font-weight: bold;">#??????</div> <div id="color-display" style="border: 1px solid #000000; padding-left: 50px; margin: 2px 2px 2px 2px; float: right;">&nbsp;</div>
        
    </td>
    </tr>
</table>

<?php 
function wpv_get_color($red, $green, $blue) {
    $red = $red < 0 ? 0 : $red;
    $green = $green < 0 ? 0 : $green;
    $blue = $blue < 0 ? 0 : $blue;

    return str_pad(dechex($red), 2, "0", STR_PAD_LEFT) . str_pad(dechex($green), 2, "0", STR_PAD_LEFT) . str_pad(dechex($blue), 2, "0", STR_PAD_LEFT);
}
?>