<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Search Engine Indexing
    |--------------------------------------------------------------------------
    |
    | Set SEO_ALLOW_INDEXING=true only on production when the site should
    | appear in search results. Dev/staging should remain false.
    |
    */

    'allow_indexing' => (bool) env('SEO_ALLOW_INDEXING', false),

];
