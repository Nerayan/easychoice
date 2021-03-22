#!/usr/bin/php

<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
    $argF = isset($argv[1]) ? $argv[1] : 'out';
    echo "<!doctype html>
<html>
<body>Hello, creating XML for U<br>";

    $fileXML = "./$argF.xml";

    // +INIT
    $SETS = parse_ini_file(__DIR__ . "/config/config.ini", true);
    try {
        $pdo = new PDO("mysql:host=" . $SETS['db']['db_server']
                . ";dbname=".$SETS['db']['db_schema']
                . ";charset=utf8",
                $SETS['db']['db_user'],
                $SETS['db']['db_passw'],
                array(PDO::ATTR_PERSISTENT => true)
            );
    } catch(PDOException $e) {echo "PDOError:" . $e->getMessage();}
    // -INIT

    // First line
    file_put_contents($fileXML, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
    file_put_contents($fileXML, '<yml_catalog date="' . date("Y-m-d H:i") . '">'."\n", FILE_APPEND);
    $shop = "<shop>\n\t<name>EasyChoice</name>\n"
        . "\t<url>easychoice.com.ua</url>\n";
    file_put_contents($fileXML, $shop, FILE_APPEND);

    $currency = "\t<currencies><currency id='UAH' rate='1'/></currencies>\n";
    file_put_contents($fileXML, $currency, FILE_APPEND);

    //Catalog Category w/ parents
    $catalog = '';
    $st_cat = $pdo->prepare("SELECT *
        FROM wp_term_taxonomy
        left JOIN wp_terms nn ON wp_term_taxonomy.term_id=nn.term_id
        WHERE count>0 and taxonomy=?");
    $st_cat -> execute(['product_cat']);
    while ($cats = $st_cat->fetch()) {
        $parnt = $cats['parent']==0? "" : " parentid='$cats[parent]'";
        $catalog .= "\t<category id='$cats[term_taxonomy_id]'$parnt>$cats[name]</category>\n";
    }
    file_put_contents($fileXML, "<catalog>\n$catalog</catalog>\n", FILE_APPEND);

    // Items
    $item = '';
    $st_itms = $pdo->prepare("
    SELECT pp.post_title, pp.ID, pp.guid, pp.post_excerpt
    , prs.meta_value price, sku.meta_value sku
    /*, group_concat(cast(term_id as char(10))) cats */
    FROM wp_posts pp
    Left Join wp_postmeta prs on pp.id=prs.post_id and prs.meta_key='_price'
    Left Join wp_postmeta sku on pp.id=sku.post_id and sku.meta_key='_sku'
/*  left join wp_term_relationships trm on pp.ID=trm.object_id
     join wp_term_taxonomy tax on trm.term_taxonomy_id=tax.term_id and tax.taxonomy='product_cat'
*/
/*  left Join wp_term_relationships trm on
    pp.id=trm.object_id
    and trm.term_taxonomy_id in (select term_id from wp_term_taxonomy where term_id=trm.taxonomy_id and taxonomy='product_cat')
*/
    WHERE pp.post_status=? and post_type=? /*and pp.id=140725*/
    ");
    $st_itms -> execute(['publish', 'product']);

$counter = 0;
while ($itm = $st_itms->fetch(PDO::FETCH_ASSOC)) {
    $counter++;
    $categoryid = '';
    $vendor     = '';
    $image      = '';

    //Get Cats and Vendor
    $st_cats = $pdo->prepare("
     select txcat.term_id, txcat.taxonomy, trms.name
     from wp_term_relationships trmr
     join wp_term_taxonomy txcat on trmr.term_taxonomy_id=txcat.term_id
     join wp_terms trms on trmr.term_taxonomy_id = trms.term_id
     where object_id = ?");
    $st_cats -> execute([$itm['ID'], ]);
    while($cat = $st_cats->fetch(PDO::FETCH_ASSOC)) {
        //	print "$cat[term_id]    $cat[taxonomy]   $cat[name]\n";
        if ($cat['taxonomy']=='product_cat') {
            $categoryid .= "    <categoryid>$cat[term_id]</categoryid>\n";}
        if ($cat['taxonomy']=='pa_proizvoditel') {
            $vendor .= "    <vendor>$cat[name]</vendor>\n";}
    }

    //Get IMAGES
    $st_img = $pdo->prepare("select permalink im
        from wp_yoast_indexable
        where post_parent = ? and object_sub_type='attachment'");
    $st_img -> execute([$itm['ID'], ]);
    while($img = $st_img->fetch(PDO::FETCH_ASSOC)) {
        $image      .= "    <image>$img[im]</image>\n";
    }

    $name = "    <name>$itm[post_title]</name>\n";
    $url  = "    <url>$itm[guid]</url>\n";
    $price= "    <price>$itm[price]</price>\n";
    
    $description= "    <description>$itm[post_excerpt]</description>\n";

// foreach(explode(',', $itm['cats']) as $cat){
//    $categoryid .= "    <categoryid>$cat</categoryid>\n";
// }

    // Concatenate ITEM
    $item .= "  <item id='$itm[sku]' wp_id='$itm[ID]'>\n$name$url$price$categoryid$image$description$vendor  </item>\n";
}

    file_put_contents($fileXML, "<items>\n$item</items>", FILE_APPEND);

    file_put_contents($fileXML, "\n</shop>", FILE_APPEND);
    echo("Posts counter: $counter<br>
</body>
</html>");
?>
