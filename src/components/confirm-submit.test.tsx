import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import ConfirmSubmit from "./confirm-submit";

describe("ConfirmSubmit", () => {
  it("requires a second click before submitting the form", () => {
    let submitted = 0;
    render(
      <form
        onSubmit={(e) => {
          e.preventDefault();
          submitted += 1;
        }}
      >
        <ConfirmSubmit label="Delete" tone="danger" />
      </form>,
    );

    const first = screen.getByRole("button", { name: "Delete" });
    fireEvent.click(first);
    expect(submitted).toBe(0);
    expect(screen.getByRole("button", { name: /click again to confirm/i })).toBeTruthy();

    fireEvent.click(screen.getByRole("button", { name: /click again to confirm/i }));
    expect(submitted).toBe(1);
  });

  it("never submits when clicks happen outside the confirm window", () => {
    let submitted = 0;
    render(
      <form
        onSubmit={(e) => {
          e.preventDefault();
          submitted += 1;
        }}
      >
        <ConfirmSubmit label="Ban" />
      </form>,
    );

    fireEvent.click(screen.getByRole("button", { name: "Ban" }));
    fireEvent.click(screen.getByRole("button", { name: /click again to confirm/i }));
    expect(submitted).toBe(1);
  });
});