<?php

namespace utils;


class DataProvider extends Webconnector
{

    /**
     * Pick random postcode from the array
     */
    public function randomPostcode(): string
    {
        $postcodes = [
            'HA9 9UB',
            'HA9 0AJ',
            'EC2Y 9AE',
            'NW9 9AB',
            'AL10 0AJ',
            'AL10 0RP',
            'SG2 9TS',
            'SE1 7TP',
            'WD24 4RS',
            'E8 2AZ',
            'RG1 2AG'
        ];

        $rand_keys = array_rand($postcodes, 1);
        return $postcodes[$rand_keys];
    }

    /**
     * Pick random school postcode from the array
     */
    public function randomSchoolPostcode(): string
    {
        $postcodes = [
            'HA9',
            'EC2Y',
            'NW9',
            'AL10',
            'SG2',
            'SE1',
            'WD24',
            'E8',
            'RG1'
        ];

        $rand_keys = array_rand($postcodes, 1);
        return $postcodes[$rand_keys];
    }

    /**
     * To generate a random string
     * @param int $length
     * @return string
     */
    public function getRandomWord(int $length=null): string
    {
        if ($length === null) {
            $word = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
            shuffle($word);
            return substr(implode($word), 0, 10);
        } else {
            $word = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
            shuffle($word);
            return substr(implode($word), 0, $length);
        }
    }

    /**
     * Generate random email address
     *
     * @return string
     */
    public function randomEmailAddress(): string
    {
        $emailAddress = 'qatester_' . rand(1, 1000000) . '@comicrelief.com';
        return $emailAddress;
    }

    /**
     * Generate uk mobile number
     * @return string
     */
    public function generateValidUKMobileNumber(): string
    {
        $phonenumber = '0' . (string)mt_rand(70, 79) . (string)mt_rand(1000, 5000) . (string)mt_rand(6000, 9000);
        return $phonenumber;
    }





}