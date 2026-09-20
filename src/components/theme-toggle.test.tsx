import { fireEvent, render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it } from "vitest";
import { THEME_STORAGE_KEY, ThemeProvider } from "./theme-provider";
import ThemeToggle from "./theme-toggle";

beforeEach(() => {
  localStorage.clear();
  document.documentElement.classList.remove("dark");
});

describe("ThemeToggle", () => {
  it("switches from light to dark on click", () => {
    render(
      <ThemeProvider>
        <ThemeToggle />
      </ThemeProvider>,
    );

    const toggle = screen.getByRole("button", { name: /switch to dark mode/i });
    expect(toggle).toBeTruthy();
    expect(document.documentElement.classList.contains("dark")).toBe(false);

    fireEvent.click(toggle);

    expect(screen.getByRole("button", { name: /switch to light mode/i })).toBeTruthy();
    expect(document.documentElement.classList.contains("dark")).toBe(true);
    expect(localStorage.getItem(THEME_STORAGE_KEY)).toBe("dark");
  });

  it("honors a stored dark preference on first render", () => {
    localStorage.setItem(THEME_STORAGE_KEY, "dark");

    render(
      <ThemeProvider>
        <ThemeToggle />
      </ThemeProvider>,
    );

    expect(screen.getByRole("button", { name: /switch to light mode/i })).toBeTruthy();
    expect(document.documentElement.classList.contains("dark")).toBe(true);
  });
});