import csv
import json
import os

# Get path to user's downloads folder
downloads_path = os.path.join(os.path.expanduser("~"), "Downloads")

# Set input and output file paths
csv_file_path = os.path.join(downloads_path, "CSV Test - Output.csv")
json_file_path = os.path.join(downloads_path, "products.json")

# Read CSV file
with open(csv_file_path) as f:
    reader = csv.reader(f)
    data = [row for row in reader]

# Extract headers and rows
headers = data[0][1:]
rows = data[1:]

# Convert to dictionary
result = {}
table_name = data[0][0]
for row in rows:
    product = row[0]
    size_prices = {}
    for i, price in enumerate(row[1:]):
        wood = headers[i]
        size_prices[wood] = int(price)
    result[product] = size_prices

# Write JSON file
with open(json_file_path, 'w') as f:
    json.dump({table_name: result}, f, indent=2)
