"""Extract only tariff rows from a CalcuPost SQL dump, without executing SQL."""

import csv
import hashlib
import json
import re
import sys
from pathlib import Path


def extract(path):
    raw = Path(path).read_bytes()
    sql = raw.decode("utf-8-sig")
    blocks = re.findall(r"INSERT INTO `tarifario`\s*\(([^;]+?)\)\s*VALUES\s*(.*?);", sql, re.S)
    if not blocks:
        raise ValueError("No tariff INSERT found")
    tariffs = []
    for header, values in blocks:
        columns = re.findall(r"`([^`]+)`", header)
        for record in re.findall(r"\(([^()]*)\)", values):
            fields = next(csv.reader([record], delimiter=",", quotechar="'", skipinitialspace=True))
            if len(fields) != len(columns):
                raise ValueError("Invalid tariff column count")
            row = dict(zip(columns, fields))
            row.pop("id")
            for column, value in row.items():
                if column == "categoria":
                    continue
                if value == "NULL":
                    row[column] = None
                elif not re.fullmatch(r"\d+(?:\.\d+)?", value):
                    raise ValueError(f"Invalid numeric value: {column}")
            tariffs.append(row)
    return {"source": Path(path).name, "source_sha256": hashlib.sha256(raw).hexdigest(), "tariffs": tariffs}


if __name__ == "__main__":
    data = extract(sys.argv[1])
    assert len(data["tariffs"]) == 283
    assert len({row["categoria"] for row in data["tariffs"]}) == 15
    Path(sys.argv[2]).write_text(json.dumps(data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print("Extracted 283 tariffs in 15 categories.")
