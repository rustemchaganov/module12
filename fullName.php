<?php

include "persons.php";
const MALE = 'мужской';
const FEMALE = 'женский';
const UNDEFINED = 'неопределенный';

// Разбиение и объединение ФИО

function getFullNameFromParts(string $surname, string $name, string $patronymic): string
{
    return "$surname $name $patronymic";
}

function getPartsFromFullName(string $string, string $separator = ' '): array
{
    $nameParts = explode($separator, $string);

    return [
        'surname' => $nameParts[0] ?? null,
        'name' => $nameParts[1] ?? null,
        'patronymic' => $nameParts[2] ?? null,
    ];
}

//Сокращение ФИО

function getShortName(string $fullName): string
{
    $nameParts = getPartsFromFullName($fullName);
    $firstCharSurname = mb_substr($nameParts['surname'], 0, 1);

    return sprintf('%s %s.', $nameParts['name'], $firstCharSurname);
}

//Функция определения пола по ФИО

function getGenderFromName(string $fullName): string
{
    $gender = 0;
    $nameParts = getPartsFromFullName($fullName);

    if ($nameParts['surname'] !== null) {
        if (mb_substr($nameParts['surname'], -1) === 'и') {
            $gender++;
        } elseif (mb_substr($nameParts['surname'], -2) === 'ва') {
            $gender--;
        }
    }

    if ($nameParts['name'] !== null) {
        $nameLastLetter = mb_substr($nameParts['name'], -1);
        if ($nameLastLetter === 'й' || $nameLastLetter === 'н') {
            $gender++;
        } elseif ($nameLastLetter === 'а') {
            $gender--;
        }
    }

    if ($nameParts['patronymic'] !== null) {
        if (mb_substr($nameParts['patronymic'], -2) === 'ич') {
            $gender++;
        } elseif (mb_substr($nameParts['patronymic'], -3) === 'вна') {
            $gender--;
        }
    }

    if ($gender > 0) {
        return MALE;
    }
    if ($gender < 0) {
        return FEMALE;
    }

    return UNDEFINED;
}

//Определение возрастно-полового состава

function getGenderDescription(array $persons): string
{
    $personCount = count($persons);
    if ($personCount < 1) {
        return '';
    }
    $namesArray = [];
    $maleCounter = 0;
    $femaleCounter = 0;
    $undefinedCounter = 0;

    foreach ($persons as $person) {
        if (!isset($person['fullname'])) {
            continue;
        }
        $namesArray[] = getGenderFromName($person['fullname']);
    }

    foreach ($namesArray as $value) {
        switch ($value) {
            case MALE:
            {
                $maleCounter++;
                break;
            }
            case FEMALE:
            {
                $femaleCounter++;
                break;
            }
            case UNDEFINED:
            {
                $undefinedCounter++;
                break;
            }
        }
    }

    $malePercent = round($maleCounter / $personCount * 100, 1);
    $femalePercent = round($femaleCounter / $personCount * 100, 1);
    $undefinedCounter = round($undefinedCounter / $personCount * 100, 1);

    return <<<GENDERDESCRIPTION
    Гендерный состав аудитории:
    ---------------------------
    Мужчины - $malePercent%
    Женщины - $femalePercent%
    Не удалось определить - $undefinedCounter%
    GENDERDESCRIPTION;
}

// Идеальный подбор пары

function getPerfectPartner(string $surname, string $name, string $patronymic, array $persons): string
{
    $personsCount = count($persons);
    if ($personsCount < 1) {
        return '';
    }

    $firstPersonName = getFullNameFromParts(
        mb_convert_case($surname, MB_CASE_TITLE, "UTF-8"),
        mb_convert_case($name, MB_CASE_TITLE, "UTF-8"),
        mb_convert_case($patronymic, MB_CASE_TITLE, "UTF-8")
    );
    $checkGender = getGenderFromName($firstPersonName);
    $i = 0;
    do {
        $i++;
        $randomPersonIndex = rand(0, $personsCount - 1);
        $secondPersonName = $persons[$randomPersonIndex]['fullname'];
        $checkRandPersonGender = getGenderFromName($secondPersonName);
    } while (($checkGender === $checkRandPersonGender || $checkRandPersonGender === UNDEFINED) && $i < 1000);

    $firstPersonName = getShortName($firstPersonName);
    $secondPersonName = getShortName($secondPersonName);
    $randomCompatibility = rand(50, 100);

    return <<<PERFECTPARTNER
    $firstPersonName + $secondPersonName =
    Идеально на $randomCompatibility%
    PERFECTPARTNER;
}
