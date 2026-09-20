import { describe, expect, it } from "vitest";
import { csvCell, toCsv } from "./csv";

describe("csvCell", () => {
  it("renders empty cells for nullish values", () => {
    expect(csvCell(null)).toBe("");
    expect(csvCell(undefined)).toBe("");
  });

  it("leaves plain values unquoted", () => {
    expect(csvCell("road repair")).toBe("road repair");
    expect(csvCell(42)).toBe("42");
    expect(csvCell(false)).toBe("false");
  });

  it("quotes and doubles inner quotes when needed", () => {
    expect(csvCell('say "hi"')).toBe('"say ""hi"""');
    expect(csvCell("a, b")).toBe('"a, b"');
    expect(csvCell("line1\nline2")).toBe('"line1\nline2"');
  });
});

describe("toCsv", () => {
  it("emits CRLF row separators and trailing newline", () => {
    const csv = toCsv([
      ["ID", "Name"],
      [1, "Gulshan water issue"],
    ]);
    expect(csv).toBe('ID,Name\r\n1,Gulshan water issue\r\n');
  });

  it("escapes commas and quotes across cells on one row", () => {
    const csv = toCsv([
      [7, 'leakage, "severe"', "Dhaka", null],
    ]);
    expect(csv).toBe('7,"leakage, ""severe""",Dhaka,\r\n');
  });
});