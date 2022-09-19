<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "A Smarter Performance Marketing Network",
    "photo" => CDN . "images/apple-touch-icon.png",
    "description" => "vNative is a Native Performance Marketing Software which help marketeers measure conversions and Conversion rate optimization",
    "keyword" => "Native Performance Marketing, marketing saas, vnative, Performance Marketing"
));

Framework\Registry::set("seo", $seo);
