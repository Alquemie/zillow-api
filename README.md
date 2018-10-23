# Zillow API PHP Wrapper

This is a simple PHP Wrapper for the Zillow API services.

## Requirements

depends on PHP 5.4+, Guzzle 6+.

##Installation

Add ``alquemie/zillow-api`` as a require dependency in your ``composer.json`` file:

```sh
php composer.phar require alquemie/zillow-api:1.6.0
```

## Usage

```php
use ZillowApi\ZillowApiClient;

$client = new ZillowApiClient('zws-id');
```

```php
use ZillowApi\ZillowMortgageApiClient;

$client = new ZillowMortgageApiClient('partner-id');
```

Make requests with a specific API call method:

```php
// Run GetSearchResults
$response = $client->execute(
    'GetSearchResults', 
    [
        'address' => '1600 Pennsylvania Ave NW', 
        'citystatezip' => 'Washington DC 20006'
    ]
);
```

```php
// Run GetSearchResults
$response = $client->execute(
    'zillowLenderReviews', 
    [
        'nmlsId' => '12345'
    ]
);
```

Valid Zillow API calls are:

- GetZestimate
- GetSearchResults
- GetChart
- GetComps
- GetDeepComps
- GetDeepSearchResults
- GetUpdatedPropertyDetails
- GetDemographics
- GetRegionChildren
- GetRegionChart
- GetRateSummary
- GetMonthlyPayments
- CalculateMonthlyPaymentsAdvanced
- CalculateAffordability
- CalculateRefinance
- CalculateAdjustableMortgage
- CalculateMortgageTerms
- CalculateDiscountPoints
- CalculateBiWeeklyPayment
- CalculateNoCostVsTraditional
- CalculateTaxSavings
- CalculateFixedVsAdjustableRate
- CalculateInterstOnlyVsTraditional
- CalculateHELOC

Valid Zillow Mortgage API calls are:
- zillowLenderReviews

## License

MIT license.
