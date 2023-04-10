<h2>Introduction</h2>

<div style="padding:10px 0 10px 15px">
<p>The <b>tags</b> are keywords the users can attach to a subject. These tags are used to group subjects or to generate statistics on a specific subset of subjects. The tags a user can attach is free. Nevertheless, if a list of tags is defined, the system will present these <b>proposed tags</b> in a drop-down list.</p>
<p>The list of these proposed tags can be defined by forum (and by language). The lists are simple <b>csv</b> files stored in the /upload/ directory.</p>
</div>

<h2>CSV file</h2>

<div style="padding:10px 0 10px 15px">
<p>The list of tags is a simple text file with one tag per row.<br>
Optionally you can add a short description (with ; as separator).</p>
<p>ATTENTION: Tags cannot include a doublequote ["]. If you use accent characters, be sure that your file is coded in utf-8.</p>

<p>Example:</p>
<p style="color:#139613">
International<br>
National<br>
Local;State or county
</p>
</div>

<h2>To create a list of tags common to all forums</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Create a .csv file named <b>tags_en.csv</b><br>
2 - Upload your file using the administration interface (or using a ftp software)
</p>
<p class="small">Note: _en means english, you can create tags in other language: tags_fr.csv, tags_nl.csv, ...</p>
</div>

<h2>To create a list of tags specific to a forum</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Create a .csv file named <b>tags_en_0.csv</b> where "0" is the id of the section.<br>
2 - Upload your file using the administration interface (or using a ftp software)
</p>
<p class="small">Note: _en means english, you can create tags in other language: tags_fr_0.csv, tags_nl_0.csv, ...</p>
</div>
