<?php

header('Content-Type: application/json');

$registryFile =
    __DIR__ . '/data/json-files.json';

$historyFile =
    __DIR__ . '/data/history.json';


/*
|--------------------------------------------------------------------------
| INITIALIZE
|--------------------------------------------------------------------------
*/

if(!is_dir(__DIR__ . '/data')){

    mkdir(
        __DIR__ . '/data',
        0755,
        true
    );

}

if(!file_exists($registryFile)){

    file_put_contents(
        $registryFile,
        '[]',
        LOCK_EX
    );

}

if(!file_exists($historyFile)){

    file_put_contents(
        $historyFile,
        '{}',
        LOCK_EX
    );

}


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function readRegistry(){

    global $registryFile;

    $data =
        @file_get_contents(
            $registryFile
        );

    $json =
        json_decode(
            $data ?: '[]',
            true
        );

    return
        is_array($json)
            ? $json
            : [];

}


function saveRegistry($registry){

    global $registryFile;

    return file_put_contents(

        $registryFile,

        json_encode(
            $registry,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        ),

        LOCK_EX

    ) !== false;

}


function readHistory(){

    global $historyFile;

    $data =
        @file_get_contents(
            $historyFile
        );

    $json =
        json_decode(
            $data ?: '{}',
            true
        );

    return
        is_array($json)
            ? $json
            : [];

}


function saveHistory($history){

    global $historyFile;

    return file_put_contents(

        $historyFile,

        json_encode(
            $history,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        ),

        LOCK_EX

    ) !== false;

}


/*
|--------------------------------------------------------------------------
| SAFE LOCAL PATH
|--------------------------------------------------------------------------
*/

function localPath($path){

    $root =
        realpath(
            $_SERVER['DOCUMENT_ROOT']
        );


    if(!$root){

        return false;

    }


    $path =
        '/' .
        ltrim(
            $path,
            '/'
        );


    /*
     * Only allow paths inside document root.
     */

    $full =
        $root . $path;


    return $full;

}


/*
|--------------------------------------------------------------------------
| READ JSON
|--------------------------------------------------------------------------
*/

function readJsonFile($file){

    $local =
        localPath($file);


    if(
        !$local ||
        !file_exists($local) ||
        !is_file($local)
    ){

        return null;

    }


    $contents =
        @file_get_contents(
            $local
        );


    if($contents === false){

        return null;

    }


    $json =
        json_decode(
            $contents,
            true
        );


    if(
        json_last_error() !==
        JSON_ERROR_NONE
    ){

        return null;

    }


    return $json;

}


/*
|--------------------------------------------------------------------------
| LIST
|--------------------------------------------------------------------------
*/

if(
    ($_GET['action'] ?? '') === 'list'
){

    $registry =
        readRegistry();

    $history =
        readHistory();

    $files = [];


    foreach($registry as $entry){

        $publicUrl =
            $entry['url'] ?? '';

        $localFile =
            $entry['file'] ?? '';


        $data =
            readJsonFile(
                $localFile
            );


        if($data === null){

            $files[] = [

                'url' =>
                    $publicUrl,

                'file' =>
                    $localFile,

                'status' =>
                    'error',

                'items' =>
                    [],

                'raw' =>
                    null

            ];

            continue;

        }


        if(
            isset($data['url']) ||
            isset($data['name'])
        ){

            $items =
                [$data];

        }else{

            $items =
                is_array($data)
                    ? $data
                    : [];

        }


        foreach(
            $items as $index => &$item
        ){

            $key =
                md5(
                    $publicUrl .
                    '|' .
                    $index
                );


            $item['history'] =
                $history[$key] ?? [];

        }


        $files[] = [

            'url' =>
                $publicUrl,

            'file' =>
                $localFile,

            'status' =>
                'ok',

            'items' =>
                $items,

            'raw' =>
                $data

        ];

    }


    echo json_encode([

        'success' =>
            true,

        'files' =>
            $files

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| ADD
|--------------------------------------------------------------------------
*/

if(
    ($_GET['action'] ?? '') === 'add'
){

    $input =
        json_decode(
            file_get_contents(
                'php://input'
            ),
            true
        );


    $url =
        trim(
            $input['url'] ?? ''
        );

    $file =
        trim(
            $input['file'] ?? ''
        );


    if(!$url || !$file){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'URL and local path are required.'

        ]);

        exit;

    }


    if(
        !filter_var(
            $url,
            FILTER_VALIDATE_URL
        )
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Invalid public URL.'

        ]);

        exit;

    }


    if(
        !str_ends_with(
            strtolower($file),
            '.json'
        )
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Local file must be a JSON file.'

        ]);

        exit;

    }


    $registry =
        readRegistry();


    foreach($registry as $entry){

        if(
            $entry['url'] === $url
        ){

            echo json_encode([

                'success' =>
                    false,

                'error' =>
                    'This JSON URL is already registered.'

            ]);

            exit;

        }

    }


    /*
     * Verify file exists.
     */

    if(
        readJsonFile($file) === null
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'The local JSON file could not be found or contains invalid JSON.'

        ]);

        exit;

    }


    $registry[] = [

        'url' =>
            $url,

        'file' =>
            $file

    ];


    saveRegistry($registry);


    echo json_encode([

        'success' =>
            true

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| REMOVE
|--------------------------------------------------------------------------
*/

if(
    ($_GET['action'] ?? '') === 'remove'
){

    $input =
        json_decode(
            file_get_contents(
                'php://input'
            ),
            true
        );


    $url =
        trim(
            $input['url'] ?? ''
        );


    $registry =
        readRegistry();


    $newRegistry = [];


    foreach($registry as $entry){

        if(
            $entry['url'] !== $url
        ){

            $newRegistry[] =
                $entry;

        }

    }


    saveRegistry(
        $newRegistry
    );


    echo json_encode([

        'success' =>
            true

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK URL
|--------------------------------------------------------------------------
*/

if(
    ($_GET['action'] ?? '') === 'check_url'
){

    $url =
        trim(
            $_GET['url'] ?? ''
        );


    $history =
        readHistory();

    $registry =
        readRegistry();


    $previouslyUsed =
        false;

    $currentLocation =
        null;


    /*
     * Historical URLs.
     */

    foreach(
        $history as $entries
    ){

        foreach(
            $entries as $entry
        ){

            if(
                ($entry['url'] ?? '') === $url
            ){

                $previouslyUsed =
                    true;

            }

        }

    }


    /*
     * Current URLs.
     */

    foreach(
        $registry as $entry
    ){

        $data =
            readJsonFile(
                $entry['file']
            );


        if($data === null){

            continue;

        }


        if(
            isset($data['url'])
        ){

            $items =
                [$data];

        }else{

            $items =
                $data;

        }


        foreach(
            $items as $item
        ){

            if(
                isset($item['url']) &&
                $item['url'] === $url
            ){

                $previouslyUsed =
                    true;

                $currentLocation =
                    $entry['url'];

            }

        }

    }


    echo json_encode([

        'success' =>
            true,

        'previouslyUsed' =>
            $previouslyUsed,

        'currentLocation' =>
            $currentLocation

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| UPDATE URL
|--------------------------------------------------------------------------
*/

if(
    ($_GET['action'] ?? '') === 'update'
){

    $input =
        json_decode(
            file_get_contents(
                'php://input'
            ),
            true
        );


    $jsonUrl =
        $input['jsonUrl'] ?? '';

    $itemIndex =
        intval(
            $input['itemIndex'] ?? -1
        );

    $newUrl =
        trim(
            $input['newUrl'] ?? ''
        );


    if(!$jsonUrl || !$newUrl){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Missing information.'

        ]);

        exit;

    }


    $registry =
        readRegistry();


    $selected =
        null;


    foreach(
        $registry as $entry
    ){

        if(
            $entry['url'] === $jsonUrl
        ){

            $selected =
                $entry;

            break;

        }

    }


    if(!$selected){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'JSON is not registered.'

        ]);

        exit;

    }


    $data =
        readJsonFile(
            $selected['file']
        );


    if($data === null){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Could not read JSON.'

        ]);

        exit;

    }


    $single =
        isset($data['url']) ||
        isset($data['name']);


    if($single){

        $items =
            [$data];

    }else{

        $items =
            $data;

    }


    if(
        !isset(
            $items[$itemIndex]
        )
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Invalid JSON item.'

        ]);

        exit;

    }


    $oldUrl =
        $items[$itemIndex]['url']
        ?? '';


    /*
     * Record old URL.
     */

    $history =
        readHistory();


    $key =
        md5(
            $jsonUrl .
            '|' .
            $itemIndex
        );


    if(
        !isset(
            $history[$key]
        )
    ){

        $history[$key] =
            [];

    }


    if(
        $oldUrl !== '' &&
        $oldUrl !== $newUrl
    ){

        $history[$key][] = [

            'url' =>
                $oldUrl,

            'date' =>
                date(
                    'Y-m-d H:i:s'
                )

        ];

    }


    saveHistory(
        $history
    );


    /*
     * Update URL.
     */

    $items[$itemIndex]['url'] =
        $newUrl;


    if($single){

        $newData =
            $items[0];

    }else{

        $newData =
            $items;

    }


    $local =
        localPath(
            $selected['file']
        );


    if(
        !$local ||
        !file_exists($local)
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Local JSON file does not exist.'

        ]);

        exit;

    }


    $encoded =
        json_encode(
            $newData,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );


    if(
        file_put_contents(
            $local,
            $encoded,
            LOCK_EX
        ) === false
    ){

        echo json_encode([

            'success' =>
                false,

            'error' =>
                'Could not write JSON file. Check permissions.'

        ]);

        exit;

    }


    echo json_encode([

        'success' =>
            true,

        'oldUrl' =>
            $oldUrl,

        'newUrl' =>
            $newUrl

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| UNKNOWN
|--------------------------------------------------------------------------
*/

echo json_encode([

    'success' =>
        false,

    'error' =>
        'Unknown action.'

]);
