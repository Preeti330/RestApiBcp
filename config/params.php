<?php

$paginationParams = [
    'pageParam',
    'pageSizeParam',
    'params',
    'totalCount',
    'defaultPageSize',
    'pageSizeLimit'
];

return [
    'frontendURL' => 'http://localhost/frontend/',
    'supportEmail' => 'admin@example.com',
    'adminEmail' => 'admin@example.com',
    'jwtSecretCode' => 'someSecretKey',
    'user.passwordResetTokenExpire' => 3600,
    'paginationParams' => $paginationParams,



     /*
    'TokenEncryptionKey' => '234234rdfedcecrfcf',
    'TokenID' => 'Ssdfkm0c42c2r24crr2',
    'JwtIssuer' => 'ChangeThisToIssuer',
    'JwtAudience' => 'ChangeThisToAudience',
    'JwtExpire' => 3600,
    'DefaultSignupRole' => 'member',*/


];
