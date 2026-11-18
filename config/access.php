<?php

return [
    'admin_user_ids' => array_values(array_filter(
        array_map(
            'trim',
            explode(',', env('ADMIN_USER_IDS', ''))
        ),
        fn ($id) => $id !== ''
    )),
];