<?php

$files_to_insert = array( 'include/parser.php' );
$files_to_add    = array( 'include/parser.php' );

/* INSERT BEFORE */

$search_file['include/parser'] = array(
	"\t\$pattern_callback[] = '%\[url\]([^\[]*?)\[/url\]%';",
	"\t\$replace_callback[] = 'handle_url_tag(\$matches[1])';",
	"\treturn substr(\$text, 1);",
);
$insert_file['include/parser'] = array(
	"\t\$pattern_callback[] = '%\[embed\]([^\[]*?)\[/embed\]%';\n",
	"\t\$replace_callback[] = 'handle_embed_tag(\$matches[1])';\n",
	"\t\$text = str_replace( '[embed]embed_', '[embed]', \$text );\n",
);

/* INSERT AFTER */
$search_add_file['include/parser'] = array(
	"// Make sure no one attempts to run this script \"directly\"\nif (!defined('PUN'))\n\texit;",
	"function do_clickable(\$text)\n{\n\t\$text = ' '.\$text;",
);
$insert_add_file['include/parser'] = array(
	"\n\nrequire_once PUN_ROOT . 'include/parser.oembed.php';",
	"\t\$text = do_embed(\$text);",
);

?>