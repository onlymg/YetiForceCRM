<?php

/**
 * Inventory UnitPrice Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_UnitPrice_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'UnitPrice';
	protected $defaultLabel = 'LBL_UNIT_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'price';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $summationValue = false;
	protected $maximumLength = '99999999999999999999';
	protected $purifyType = \App\Purifier::NUMBER;
	/** {@inheritdoc} */
	protected $params = ['currency_convert'];

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		$value = \App\Fields\Double::formatToDisplay($value);
		if (isset($rowData['currency']) && $currencySymbol = \App\Fields\Currency::getById($rowData['currency'])['currency_symbol'] ?? '') {
			$value = \CurrencyField::appendCurrencySymbol($value, $currencySymbol);
		}

		return $value;
	}

	/** {@inheritdoc} */
	public function getEditValue(array $itemData, string $column = '')
	{
		$value = parent::getEditValue($itemData, $column);
		return \App\Fields\Double::formatToDisplay($value, false);
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue["{$value}"])) {
			$this->dbValue["{$value}"] = App\Fields\Double::formatToDb($value);
		}
		return $this->dbValue["{$value}"];
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($isUserFormat) {
			$value = $this->getDBValue($value, $columnName);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($this->maximumLength < $value || -$this->maximumLength > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function compare($value, $prevValue, string $column): bool
	{
		return \App\Validator::floatIsEqual((float) $value, (float) $prevValue, 8);
	}

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$data = parent::getConfigFieldsData();
		$data['currency_convert'] = [
			'name' => 'currency_convert',
			'label' => 'LBL_INV_UNITPRICE_CURRENCY_CONVERT',
			'uitype' => 56,
			'maximumlength' => '1',
			'typeofdata' => 'C~O',
			'tooltip' => 'LBL_INV_UNITPRICE_CURRENCY_CONVERT_DESC',
			'purifyType' => \App\Purifier::INTEGER,
			'defaultvalue' => 0
		];

		return $data;
	}
}
