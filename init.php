<?php

//Отключаем скидки у акционых товаров



use Bitrix\Main\Loader;
Loader::includeModule('catalog');
AddEventHandler("sale", "OnCondSaleActionsControlBuildList", Array("SaleActionDeactivateDiscount", "GetControlDescr"));//корзина
//AddEventHandler("catalog", "OnCondCatControlBuildList", Array("CatalogCondCtrlDiscAction2", "GetControlDescr"));//каталог
class SaleActionDeactivateDiscount extends CGlobalCondCtrlComplex
{
    function __construct()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
    }

    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'NoActionDiscount';
    }
    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();
        $arResult = array(
            'controlgroup' => true,
            'group' =>  false,
            'label' => 'Другое',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => array()
        );
        foreach ($arControls as $arOneControl)
        {
            $arResult = array(
                'controlId' => $arOneControl['ID'],
                'group' => false,
                'label' => $arOneControl['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => array(
                    array(
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => $arOneControl['PREFIX']
                    ),
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE'])
                )
            );
        }
        return $arResult;
    }

    public static function GetControls($strControlID = false)
    {
        $arControlList = array(
            'NoActionDiscount' => array(
                'ID' => 'NoActionDiscount',
                'FIELD' => 'DISC_ACTION',
                'FIELD_TYPE' => 'string',
                'LABEL' => 'Распространяет на акционые товары',
                'PREFIX' => 'Распространяет на акционые товары',
                'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ, BT_COND_LOGIC_NOT_CONT)),
                'JS_VALUE' => array(
                    'type' => 'select',
                    'values' => array(
                        //'true' => 'Да',
                        'false' => 'Нет'
                    ),
                ),
                'PHP_VALUE' => ''
            ),
        );
        if ($strControlID === false)
        {
            return $arControlList;
        }
        elseif (isset($arControlList[$strControlID]))
        {
            return $arControlList[$strControlID];
        }
        else
        {
            return false;
        }

    }

    public static function Generate($arConditions, $arParams)
    {

        $resultValues = array();
        $arValues = false;

        if (is_string($arControl))
        {
            $arControl = static::GetControls($arControl);
        }
        $boolError = !is_array($arControl);

        if (!$boolError)
        {
            $arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
            $boolError = (false === $arValues);
        }


        if($arOneCondition['logic']=='Equal')
        {
            $logic='true';
        }
        else
        {
            $logic='false';
        }

        $strResult  = '(SaleActionDeactivateDiscount::qDiscAction('.$arParams['BASKET_ROW'].'))=='.$logic;
        return  $strResult;

    }

    public static function qDiscAction($row)
    {
        global $gParams;



        $db_props = CIBlockElement::GetProperty(4, $row['PRODUCT_ID'], array("sort" => "asc"), Array("CODE"=>"NOMENCLATURE"));
        while($ar_props = $db_props->Fetch())
        {
            $ar = explode(':', $ar_props['VALUE']);
            $arCode[$ar[0]] = true;

        }

        foreach ($gParams['price_code'] as $xmlID)
        {
            if(!empty($arCode[$xmlID]))
                return true;
        }

        return false;
    }

    public static function ApplyValues($arOneCondition, $arControl)
    {

        return (isset($arOneCondition['True']) && $arOneCondition['True'] == 'True');
    }
}
?>
