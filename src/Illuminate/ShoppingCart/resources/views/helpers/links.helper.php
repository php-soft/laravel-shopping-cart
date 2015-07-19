<?php

/**
 * Array View Helper
 * 
 * @param  array  $items
 * @return array
 */
function helper_links($items = [])
{
    $hrefSelf = Request::fullUrl();

    $links = [
        'self' => [
            'href' => $hrefSelf,
            'type' => 'application/json; version=1.0',
        ]
    ];

    if (count($items)) {
        $last = $items[count($items) - 1];

        $currentRoute = Route::current();
        $queries = Input::all();
        $queries = array_merge($queries, [
            'cursor' => $last->id,
        ]);
        $hrefNext = url($currentRoute->getPath()) . '?' . http_build_query($queries);

        $links['next'] = [
            'href' => $hrefNext,
            'type' => 'application/json; version=1.0',
        ];
    }

    return $links;
}
