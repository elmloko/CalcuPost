"""Extract the explicit Dirección Comercial amounts from the reviewed 2026 workbook.

Usage: python scripts/extract_commercial_tariffs_2026.py input.xlsx > database/data/commercial_tariffs_2026.json
"""

import json
import re
import sys
from decimal import Decimal

from openpyxl import load_workbook


def kilograms(value):
    if isinstance(value, (int, float)):
        return Decimal(str(value)) / 1000
    match = re.fullmatch(r"([\d,.]+)\s*(kg|g)", str(value).strip(), re.I)
    if not match:
        raise ValueError(f"Unrecognized weight: {value!r}")
    amount = Decimal(match.group(1).replace(",", "."))
    return amount if match.group(2).lower() == "kg" else amount / 1000


workbook = load_workbook(sys.argv[1], read_only=True, data_only=True)
specs = {
    "ENCOMIENDA": ("Encomiendas Post. Inter", 6, 29, "B", {"dest_a": "F", "dest_b": "L", "dest_c": "R", "dest_d": "X", "dest_e": "AD"}),
    "EMS NAT": ("EMS Nacional", 6, 27, "B", {"nacional": "F", "cui_int": "K", "pro_dentro": "O", "cui1": "S", "cui2": "W"}),
    "EMS INT": ("EMS Internacional", 6, 29, "A", {"dest_a": "D", "dest_b": "L", "dest_c": "U", "dest_d": "AC", "dest_e": "AK"}),
    "ECA INT": ("ECA Internacional", 6, 14, "B", {"dest_a": "F", "dest_b": "K", "dest_c": "P", "dest_d": "U", "dest_e": "Z"}),
}
output = {}
for category, (sheet_name, first, last, weight_column, columns) in specs.items():
    sheet = workbook[sheet_name]
    previous_max = Decimal("0")
    rows = []
    for index in range(first, last + 1):
        maximum = kilograms(sheet[f"{weight_column}{index}"].value)
        minimum = previous_max + Decimal("0.001")
        if maximum < minimum:
            raise ValueError(f"Invalid band in {sheet_name}!{index}")
        row = {"peso_min_kg": f"{minimum:.3f}", "peso_max_kg": f"{maximum:.3f}"}
        for destination, column in columns.items():
            amount = sheet[f"{column}{index}"].value
            if not isinstance(amount, (int, float)) or amount < 0:
                raise ValueError(f"Invalid amount in {sheet_name}!{column}{index}: {amount!r}")
            row[destination] = int(amount) if int(amount) == amount else amount
        rows.append(row)
        previous_max = maximum
    output[category] = rows

postcards = workbook["ECA Internacional"]
special_tariffs = {
    "ECA INT": {
        "Tarjetas postales": {
            zone: postcards[f"{column}15"].value
            for zone, column in {"Destino A": "F", "Destino B": "K", "Destino C": "P", "Destino D": "U", "Destino E": "Z"}.items()
        }
    }
}

print(json.dumps({"tariffs": output, "special_tariffs": special_tariffs}, ensure_ascii=False, indent=2))
