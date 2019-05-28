<a href="http://ojs.wittenberg.edu/index.php/wueasj"><img src="http://www.wittenberg.edu/sites/default/files/images/easjournal.gif" height=100 border=0 align=right></a>
         <h2>
       Index to <cite>Wittenberg University East Asian Studies Journal</cite>, <?=$start_date?>-<?=$end_date?> </h2>
         
         <hr>
<a name=results></a>

         <table border=0>
<tr><td>
<form method=get>
<input type="hidden" name="index" value="eas" />
       <!-- strong>This is an index only. For access to the journal itself, please see a librarian.</strong><P --> 
       Search for: <input type=text name=search value="" size=30> in
<select name=fields>
 <option value=any SELECTED>Any Field</option>
 <option value=author>Author</option>
 <option value=title>Article Title</option>
 <option value=year>Year</option>
</select>
<BR>
       Genres: 
Essay <input type=checkbox name=genre[] value=essay CHECKED> |
Poem <input type=checkbox name=genre[] value=poem CHECKED> |
Play <input type=checkbox name=genre[] value=play CHECKED> |
Artwork <input type=checkbox name=genre[] value=artwork CHECKED> |
Fiction <input type=checkbox name=genre[] value=fiction CHECKED> | 
Review <input type=checkbox name=genre[] value=review CHECKED>

<BR><strong> or Browse by: </strong> <a href="?index=<?=INDEX?>&browse=author#results">Author</a> | <a href="?index=<?=INDEX?>&browse=title#results">Title</a> | <a href="?index=<?=INDEX?>&browse=genre#results">Genre</a> | <a href="?index=<?=INDEX?>&browse=year#results">Year</a>

<P><input type=submit>
</form>
<td width="10%">
</table>