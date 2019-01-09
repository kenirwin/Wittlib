<?php
if (isset($no_results)) { print "<p style=\"border: 1px solid #900; padding: .5em; width: 25em; text-align: center\">No Results Found. Try another search.</p>"; }
?>

<form method=GET>
<table border=0>
<tr><th align="left">Search across fields:</th><td> Subject<input type=checkbox name="fields[]" value="subject" CHECKED>
Title<input type=checkbox name="fields[]" value="title" CHECKED>
<!--Page<input type=checkbox name="fields[]" value="page">-->
Date<input type=checkbox name="fields[]" value="date">
Year<input type=checkbox name="fields[]" value="year" CHECKED>
<td>
</tr>
      <tr><th valign="top" align="left">Choose Database to Search:</th><td> Torch (Student Newspaper) <input type=radio name="database" value="torch" CHECKED><br> 
      Wittenberg Magazine (Alumni Magazine)&nbsp;<input type=radio name="database" value="alum"> </td></tr>

<tr><th align="left" valign="top"><input type="hidden" name="setup" value="torch.config">
      Words or parts of words to search:</th>
    <td><input type=text name="terms" id="terms" size=30>
<input type="submit" value="Search!">
      <tr><td colspan=2 bgcolor=bbbbbb align=center><strong>Please note: using fewer search terms will yield more and often better results.</strong></td><tr>
</table>
<!--TR><td colspan=2 align="center"-->


</form>

