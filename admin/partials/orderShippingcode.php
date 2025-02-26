<?php
/*
Plugin Name: PostNL-ECS
Plugin URI: http://www.postnl.nl/
Description: PostNL ECS Fulfilment Plugin
Author: PostNL
Author URI: http://www.postnl.nl/
*/

/**
 * Order export shipping ID settings
 */

function getPostNLEcsShippingCode($shippingCountry, $order) {

    $shippingOptionsJson = $order->get_meta('_postnl_delivery_options');

    if(is_array($shippingOptionsJson))
        return false;

    $shippingOptions = json_decode($shippingOptionsJson,true);

    $saoArray = [
        'Morning10',
        'Morning',
        'Morning12',
        'Evening',
        'Standard'
    ];
    if($shippingOptions) {

        $shipmentOptions = '';
        if(isset($shippingOptions['shipmentOptions']))
            $shipmentOptions = $shippingOptions['shipmentOptions'];



        $homeAddressOnly = '';
        $sinatureOption = '';


        if(isset($shipmentOptions['only_recipient']) && ($shipmentOptions['only_recipient'] != 0))
            $homeAddressOnly = '_SAO';

        if(isset($shipmentOptions['signature']) && ($shipmentOptions['signature'] != 0))
            $sinatureOption = '_SIG';




        if(isset($shippingOptions['packageType'])) {

            if($shippingOptions['packageType'] == 'mailbox') {
                if( strtolower($shippingCountry) === 'nl')
                    return '02928';
                else
                    return get_outside_nl_shipping($shippingCountry);


            }

            if($shippingOptions['packageType'] == 'letter' || $shippingOptions['packageType'] == 'digital_stamp')
                return 'NA';


            if($shippingOptions['packageType'] == 'package') {

                $postNlCode = getpostnlMappingCodes($shippingOptions, $shippingCountry);
                if(in_array($postNlCode,$saoArray))
                    $postNlCode = $postNlCode.$sinatureOption.$homeAddressOnly;
                return $postNlCode;

            }



        }




    }

    return false;




}

function getpostnlMappingCodes($options, $countryCode) {



    if(isset($options['deliveryType'])) {
        if(isset($options['isPickup']) && $options['deliveryType'] == 'pickup' && $options['isPickup']){
            if(strtolower($countryCode) === 'nl')
                return  '03533';
            if(strtolower($countryCode) === 'be')
                return '04936';
            else
                return 'NA';
        }



        if($options['deliveryType'] == 'morning')
            return 'Morning';

        if(strtolower($countryCode) !== 'nl')
            return get_outside_nl_shipping($countryCode);

        if($options['deliveryType'] == 'evening')
            return 'Evening';

        if($options['deliveryType'] == 'standard')
            return 'Standard';




    }

    return 'PNLP';




}

function ecs_eu_country_check($country_code) {
    $euro_countries = array(
                            'AT',
                            'BG',
                            'CY',
                            'CZ',
                            'DE',
                            'DK',
                            'EE',
                            'ES',
                            'FI',
                            'FR',
                            'GR',
                            'HR',
                            'HU',
                            'IE',
                            'IT',
                            'LT',
                            'LU',
                            'LV',
                            'MC',
                            'MT',
                            'PL',
                            'PT',
                            'RO',
                            'SE',
                            'SI',
                            'SK',
                            'VA'
                    );

    return in_array( $country_code, $euro_countries);
}

function get_outside_nl_shipping($countryCode) {
    if(strtoupper($countryCode) == 'BE')
        return '04946';
    if(ecs_eu_country_check(strtoupper($countryCode)))
        return '04952';
    else
        return '04945';

}

function postnl_fulfilment_shipping_age_check ($shippingCountry, $order) {
    $shippingOptionsJson = $order->get_meta('_postnl_delivery_options');
    if(is_array($shippingOptionsJson))
        return '';

    $shippingOptions = json_decode($shippingOptionsJson,true);
    $ageCheck = '';
    $ageCheckCode = 'LC1';

    if(isset($shippingOptions['shipmentOptions']) && $shippingOptions['shipmentOptions']) {

        $shipmentOptions = $shippingOptions['shipmentOptions'];
        if(isset($shipmentOptions['age_check']) && ($shipmentOptions['age_check'] != 0))
            $ageCheck =  $ageCheckCode;

    }

    return  $ageCheck;
}