<table border=0>
<tr><td>
<form method=get>
<input type="hidden" name="index" value="pholeos" />

             Search for: <input type=text name=search value="" size="30"> in
<select name=fields>
 <option value=any SELECTED>Any Field</option>
 <option value=author>Author</option>
 <option value=title>Article Title</option>
 <option value=year>Year</option>
</select>
<BR>
<table><tr><td valign="top">Genres:</td><td>
<?php
             function GenreCheckbox($fieldname) {
                 $label = ucwords($fieldname);
                 if (! array_key_exists('genre', $_REQUEST)) { $checked = "CHECKED"; }
                 elseif (in_array($fieldname, $_REQUEST['genre'])){ $checked = "CHECKED"; }
                 else { $checked = ""; }
                 return $label .' <input type="checkbox" name="genre[]" value="'.$fieldname.'" '.$checked.'/>';
             }
     print ( GenreCheckbox('research') . ' | ' . PHP_EOL );
print ( GenreCheckbox('technique') . ' | ' . PHP_EOL );
print ( GenreCheckbox('essay') . ' | ' . PHP_EOL );
print ( GenreCheckbox('obituary') . PHP_EOL );
print '<br>';
print ( GenreCheckbox('survey') . ' | ' . PHP_EOL );
print ( GenreCheckbox('map') . ' | ' . PHP_EOL );
print ( GenreCheckbox('trip report') . ' | ' . PHP_EOL );
print ( GenreCheckbox('gear review') . PHP_EOL );

print '<br>';
print ( GenreCheckbox('poem') . ' | ' . PHP_EOL );
print ( GenreCheckbox('photo') . ' | ' . PHP_EOL );
print ( GenreCheckbox('art') . ' | ' . PHP_EOL );
print ( GenreCheckbox('movie review') . ' | ' . PHP_EOL );
print ( GenreCheckbox('miscellaneous') . PHP_EOL );


    ?>
</td></tr></table>

<BR><strong> or Browse by: </strong> <a href="?browse=author">Author</a> | <a href="?browse=title">Title</a> | <a href="?browse=genre">Genre</a> | <a href="?browse=year">Year</a>

<P><input type=submit>
</form>
<td width="10%">
</table>