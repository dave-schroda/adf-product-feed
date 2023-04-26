import csv
import json
import os

def main():
    user_home = os.path.expanduser("~")
    downloads_folder = os.path.join(user_home, "Downloads")
    input_file = os.path.join(downloads_folder, "test.csv")
    data = []

    with open(input_file, newline='') as csvfile:
        csv_reader = csv.reader(csvfile)
        product_name = next(csv_reader)[0]

        wood_headers = next(csv_reader)
        wood_types = wood_headers[2:]

        options = []

        for row in csv_reader:
            if not row:
                continue  # Skip empty rows

            option = {
                "leaves": row[0],
                "table_size": row[1],
                "price": {}
            }
            for i, wood in enumerate(wood_types):
                wood_key = wood.lower().replace(" ", "_")
                option["price"][wood_key] = int(row[i + 2])
            options.append(option)

        wood_dict = {wood.lower().replace(" ", "_"): wood for wood in wood_types}
        data.append({
            "product_name": product_name,
            "wood": wood_dict,
            "options": options
        })

    output_file = os.path.join(downloads_folder, f"{product_name.replace(' ', '_')}.json")
    
    with open(output_file, 'w') as jsonfile:
        json.dump(data, jsonfile, indent=2)

if __name__ == "__main__":
    main()
