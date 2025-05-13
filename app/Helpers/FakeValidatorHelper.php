<?php

function is_fake_phone(string $phone): bool
{
    if (!is_string($phone)) {
        $phone = (string) $phone;
    }

    // Specific fake numbers
    $specificFakeNumbers = [
        '96789012',
        '97890123',
        '87890123',
    ];

    // Block numbers is in the list of fake numbers
    if (in_array($phone, $specificFakeNumbers, true)) {
        return true;
    }

    // Must exactly 8 digits
    if (!preg_match('/^\d{8}$/', $phone)) {
        return true;
    }

    // Must start with 8 or 9 (valid Singapore mobile numbers)
    if (!preg_match('/^[89]\d{7}$/', $phone)) {
        return true;
    }

    // Block numbers with repeated digits (e.g., 99999999, 00000000)
    if (preg_match('/^(\d)\1{7}$/', $phone)) {
        return true;
    }

    // Block numbers with repeated patterns (e.g., 88223344, 99334455)
    if (preg_match('/^(\d)\1(\d)\2(\d)\3(\d)\4$/', $phone)) {
        return true;
    }

    // Block numbers with any digit appears more than 5 times (e.g., **666666, *000*000)
    if (preg_match('/(\d)\1{4,}/', $phone)) {
        return true;
    }

    // Block numbers that are sequential (ascending or descending) (e.g., *1234567, **98765*)
    if (preg_match('/(01234|12345|23456|34567|45678|56789|67890|98765|87654|76543|65432|54321|43210)/', $phone)) {
        return true;
    }

    return false;
}

function is_fake_email(string $email): bool
{
    // Standard email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }

    // Block emails without a valid TLD (e.g., johndoe@gmail, test@com)
    if (!preg_match('/\.[a-zA-Z]{2,}$/', $email)) {
        return true;
    }

    // Extract domain from email
    $domain = substr(strrchr($email, '@'), 1);

    $disposableDomains = [
        'mailinator.com',
        'guerrillamail.com',
        'yopmail.com',
        'tempmail.com',
        'trashmail.com',
        '10minutemail.com',
        'throwawaymail.com',
        'maildrop.cc',
    ];

    // Block disposable email domains
    if (in_array(strtolower($domain), $disposableDomains)) {
        return true;
    }

    $localPart = strstr($email, '@', true) ?: $email;

    $specificFakeLocalParts = [
        'no',
        'yes',
    ];

    // Block specific fake local parts
    if (in_array($localPart, $specificFakeLocalParts, true)) {
        return true;
    }

    // Block email contain repeated patterns (e.g., aaa, zzz, 111)
    if (preg_match('/(.)\1{2,}/', $email)) {
        return true;
    }

    if (has_keyboard_smash($email)) {
        return true;
    }

    if (has_bad_words($email)) {
        return true;
    }

    return false;
}

function is_fake_name(string $name): bool
{
    // Convert to lowercase and trim spaces
    $name = strtolower(trim($name));
    $name = str_replace(' ', '', $name);

    // Must more than 1 character
    if (strlen($name) < 2) {
        return true;
    }

    $placeholderKeywords = [
        'test',
        'lorem',
        'ipsum',
        'place holder',
        'john doe',
        'no name',
        'unknown',
        'user',
        'aa',
        'bb'
    ];

    // Block name contains common placeholder keywords
    foreach ($placeholderKeywords as $keyword) {
        $keyword = strtolower(trim($keyword));
        $keyword = str_replace(' ', '', $keyword);
        if (stripos($name, $keyword) !== false) {
            return true;
        }
    }

    // Block name contain repeated patterns (e.g., aaa, zzz, 111)
    if (preg_match('/(.)\1{2,}/', $name)) {
        return true;
    }

    $unrealisticNames = [
        'Superman',
        'Batman',
        'Donald Duck',
        'Nobody',
        'Anonymous',
        'Spider-Man',
        'Spiderman',
        'Tony Stark',
        'James Bond',
        'Homer Simpson',
        'John Wick',
        'Iron Man',
        'Captain',
        'Dr Strange',
        'Obi Wan Kenobi',
        'Darth',
        'Han Solo',
        'Gandalf',
        'Jack Sparrow',
        'Wonder Woman',
        'Yoda',
        'Hulk',
        'Loki',
        'Goku',
        'Naruto',
        'Spongebob',
        'Mickey Mouse',
        'Pikachu',
        'Donald Trump',
        'Obi Wan',
        'Big Boss',
        'LOL Listing',
        'Unknown',
    ];

    // Block name contains unrealistic names
    foreach ($unrealisticNames as $keyword) {
        $keyword = strtolower(trim($keyword));
        $keyword = str_replace(' ', '', $keyword);
        if (stripos($name, $keyword) !== false) {
            return true;
        }
    }

    $specificFakeNames = [
        'Haha LOL',
        'Agent007',
        'Siao Realtor',
        'Fake Buyer',
        'Ghost Enquiry',
        'Super Seller 007',
        'Fake Buyer Pro',
        'Siao Kia Buyer',
        'LOL Listing',
    ];

    // Block contains specific fake names
    foreach ($specificFakeNames as $keyword) {
        $keyword = strtolower(trim($keyword));
        $keyword = str_replace(' ', '', $keyword);
        if (stripos($name, $keyword) !== false) {
            return true;
        }
    }
    
    if (has_keyboard_smash($name)) {
        return true;
    }

    if (has_bad_words($name)) {
        return true;
    }

    return false;
}

function has_keyboard_smash(string $field = ''): bool
{
    $field = strtolower(trim($field));
    $field = str_replace(' ', '', $field);

    $keyboardSmashPatterns = [
        'xyz',
        'adfg',
        'sdfgh',
        'lkjhg',
        'mnbvc',
        'qwerty',
        'zxcvbn',
        'poiuyt',
        'qazwsx',
        'wsxedc',
        'edcrfv',
        'rfvtgb',
        'tgbnhy',
        'yhnujm',
        'asdsdf',
        'asdkjfhg',
    ];

    // Block field contains keyboard smash keywords
    foreach ($keyboardSmashPatterns as $pattern) {
        if (stripos($field, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

function has_bad_words(string $field = ''): bool
{
    $field = strtolower(trim($field));
    $field = str_replace(' ', '', $field);

    $badWords = [
        'stupid',
        'yourmother',
        'idiot',
        'lousy',
        'fuckyou',
        'lanjiao',
        'ganina',
        'pukima',
        'cheebye',
        'wasteagent',
        'useless',
        'dumb',
        'loser',
        'screw',
        'scam',
        'trash',
        'ccb',
        'nabei',
        'dumb',
        'useless',
    ];

    // Block field contains bad words
    foreach ($badWords as $keyword) {
        if (stripos($field, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

function is_not_match_name_email($name, $email): bool
{
    if (empty($email) || empty($name)) {
        return false;
    }

    $emailUsername = explode('@', $email)[0];

    $normalizedEmailUsername = strtolower(str_replace(['.', '_', '-'], '', $emailUsername));
    $normalizedName = strtolower(str_replace(' ', '', $name));

    if (strpos($normalizedEmailUsername, $normalizedName) !== false || strpos($normalizedName, $normalizedEmailUsername) !== false) {
        return false;
    }

    $similarity = 0;
    similar_text($normalizedEmailUsername, $normalizedName, $similarity);

    return $similarity < 60;
}
