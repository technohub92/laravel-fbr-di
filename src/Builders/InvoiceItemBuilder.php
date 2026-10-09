<?php

namespace FbrDI\Builders;

class InvoiceItemBuilder
{
    protected string $hsCode = '';
    protected string $productDescription = '';
    protected float $rate = 18.00;
    protected string $uoM = 'Numbers';
    protected float $quantity = 1.0;
    protected float $totalValues = 0.0;
    protected float $valueSalesExcludingST = 0.0;
    protected float $fixedNotifiedValueOrRetailPrice = 0.0;
    protected float $salesTaxApplicable = 0.0;
    protected float $salesTaxWithheldAtSource = 0.0;
    protected float $extraTax = 0.0;
    protected float $furtherTax = 0.0;
    protected ?string $sroScheduleNo = null;
    protected ?string $sroItemSerialNo = null;
    protected float $fedPayable = 0.0;
    protected float $discount = 0.0;
    protected string $saleType = 'Goods';

    public function hsCode(string $hsCode): self
    {
        $this->hsCode = trim($hsCode);
        return $this;
    }

    public function description(string $desc): self
    {
        $this->productDescription = trim($desc);
        return $this;
    }

    public function taxRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function uom(string $uom): self
    {
        $this->uoM = trim($uom);
        return $this;
    }

    public function quantity(float $qty): self
    {
        $this->quantity = $qty;
        return $this;
    }

    public function unitPrice(float $price): self
    {
        $this->valueSalesExcludingST = round($this->quantity * $price, 2);
        $this->calculateTaxes();
        return $this;
    }

    public function salesTaxExcluding(float $amount): self
    {
        $this->valueSalesExcludingST = round($amount, 2);
        $this->calculateTaxes();
        return $this;
    }

    public function salesTaxApplicable(float $amount): self
    {
        $this->salesTaxApplicable = round($amount, 2);
        $this->updateTotalValues();
        return $this;
    }

    public function furtherTax(float $amount): self
    {
        $this->furtherTax = round($amount, 2);
        $this->updateTotalValues();
        return $this;
    }

    public function extraTax(float $amount): self
    {
        $this->extraTax = round($amount, 2);
        $this->updateTotalValues();
        return $this;
    }

    public function fedPayable(float $amount): self
    {
        $this->fedPayable = round($amount, 2);
        $this->updateTotalValues();
        return $this;
    }

    public function discount(float $amount): self
    {
        $this->discount = round($amount, 2);
        $this->updateTotalValues();
        return $this;
    }

    public function sroExemption(?string $scheduleNo, ?string $itemSerialNo = null): self
    {
        $this->sroScheduleNo = $scheduleNo;
        $this->sroItemSerialNo = $itemSerialNo;
        return $this;
    }

    public function saleType(string $saleType): self
    {
        $this->saleType = $saleType;
        return $this;
    }

    protected function calculateTaxes(): void
    {
        if ($this->rate > 0 && $this->salesTaxApplicable === 0.0) {
            $this->salesTaxApplicable = round(($this->valueSalesExcludingST * $this->rate) / 100, 2);
        }
        $this->updateTotalValues();
    }

    protected function updateTotalValues(): void
    {
        $this->totalValues = round(
            $this->valueSalesExcludingST 
            + $this->salesTaxApplicable 
            + $this->furtherTax 
            + $this->extraTax 
            + $this->fedPayable 
            - $this->discount, 
            2
        );
    }

    public function toArray(): array
    {
        $item = [
            'hsCode' => $this->hsCode ?: '8471.3010',
            'productDescription' => $this->productDescription ?: 'Standard Goods',
            'rate' => (string) (rtrim(rtrim(number_format($this->rate, 2, '.', ''), '0'), '.') . '%'),
            'uoM' => $this->uoM ?: 'Numbers',
            'quantity' => (float) $this->quantity,
            'totalValues' => (float) $this->totalValues,
            'valueSalesExcludingST' => (float) $this->valueSalesExcludingST,
            'fixedNotifiedValueOrRetailPrice' => (float) $this->fixedNotifiedValueOrRetailPrice,
            'salesTaxApplicable' => (float) $this->salesTaxApplicable,
            'salesTaxWithheldAtSource' => (float) $this->salesTaxWithheldAtSource,
            'extraTax' => (float) $this->extraTax,
            'furtherTax' => (float) $this->furtherTax,
            'sroScheduleNo' => $this->sroScheduleNo,
            'fedPayable' => (float) $this->fedPayable,
            'discount' => (float) $this->discount,
            'saleType' => $this->saleType,
            'sroItemSerialNo' => $this->sroItemSerialNo,
        ];

        return array_filter($item, fn($v) => !is_null($v));
    }
}
