import requests 
import logging
import argparse
import datetime
import csv
import json
import sys

CURRENCY_NAME_EN = {
    "USD": "US Dollar",
    "EUR": "Euro",
    "GBP": "British Pound",
    "CHF": "Swiss Franc",
    "PLN": "Polish Zloty",
    "NOK": "Norwegian Krone",
    "SEK": "Swedish Krona",
    "DKK": "Danish Krone",
    "ISK": "Icelandic Krona",
    "CZK": "Czech Koruna",
    "HUF": "Hungarian Forint",
    "UAH": "Ukrainian Hryvnia",
    "RUB": "Russian Ruble",
    "BYN": "Belarusian Ruble",
    "JPY": "Japanese Yen",
    "CNY": "Chinese Yuan Renminbi",
    "KRW": "South Korean Won",
    "TWD": "New Taiwan Dollar",
    "INR": "Indian Rupee",
    "SGD": "Singapore Dollar",
    "THB": "Thai Baht",
    "MYR": "Malaysian Ringgit",
    "IDR": "Indonesian Rupiah",
    "VND": "Vietnamese Dong",

}

class CurrencyIterator:
    def __init__(self, iso, date_from, date_to):
        self.iso = iso
        self.date_from = date_from
        self.date_to = date_to
        
        url = f'https://api.nbp.pl/api/exchangerates/rates/a/{iso}/{date_from}/{date_to}/'
        query_data = requests.get(url)
        extracted = query_data.json()
        
        self.rates = extracted['rates']
        self.currency_code = extracted['code']
        self.currency_name = CURRENCY_NAME_EN.get(self.currency_code, extracted['currency'])
       
            
        self.index = 0
        
    def __iter__(self):
        return self
    
    def __next__(self):
        if self.index >= len(self.rates):
            raise StopIteration
            
        item  = self.rates[self.index]
                
        currency_dict = {
            'ISO': self.currency_code,
            'Currency Name': self.currency_name,
            'Rate': item['mid'],
            'Date': item['effectiveDate']
        }
        
        self.index += 1
        
        return currency_dict
    
def setup_logs(level_var:int):
    if level_var == 0:
        logging.basicConfig(level = logging.WARNING)
    elif level_var == 1:
        logging.basicConfig(level = logging.INFO)
    else:
        logging.basicConfig(level = logging.DEBUG)
        
def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--iso', type = str, required = True, help = 'Currency ISO Code')
    parser.add_argument('--date_from', type = str, required = False,
                        default=(datetime.datetime.now() - datetime.timedelta(days=7)).strftime('%Y-%m-%d'),
                        help='Start date in YYYY-mm-dd format (default: today - 7 days)')
    parser.add_argument('--date_to', type = str, required = False,
                        default=(datetime.datetime.now()).strftime('%Y-%m-%d'),
                        help='End date in YYYY-mm-dd format (default: today)')
    parser.add_argument('--output_file', type = str, required = False,
                        help='Path to output file (if not set, prints to console)')
    parser.add_argument('--output_format', choices=['CSV', 'JSON'], required = False, default='CSV',
                        help='Output format: CSV or JSON (default: CSV)')
    parser.add_argument('--verbose_level', choices=[0, 1, 2], required = False, default=0,
                        help='Logging verbosity level: 0=Errors/Warnings, 1=+Info, 2=+Debug')
    arguments = parser.parse_args()

    iterator = CurrencyIterator(arguments.iso, arguments.date_from, arguments.date_to)
    data = list(iterator)
    setup_logs(arguments.verbose_level)

    if arguments.output_file:
        if arguments.output_format == 'JSON':
            with open(arguments.output_file, 'w', encoding='utf-8') as f:
                for item in data:
                    f.write(json.dumps(item) + '\n')
        else:
            with open(arguments.output_file, 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=data[0].keys())
                writer.writeheader()
                writer.writerows(data)
    else:
        if arguments.output_format == 'JSON':
            for item in data:
                print(json.dumps(item))
        else:
            writer = csv.DictWriter(sys.stdout, fieldnames=data[0].keys())
            writer.writeheader()
            writer.writerows(data)


if __name__ == "__main__":
    main()