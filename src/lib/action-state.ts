export type ActionState = {
  message?: string;
  errors?: Record<string, string[]>;
} | null;

export function fieldErrors(formatted: {
  fieldErrors: Record<string, string[]>;
  formErrors: string[];
}): Record<string, string[]> {
  if (formatted.formErrors.length > 0) {
    return { _form: formatted.formErrors };
  }
  return formatted.fieldErrors;
}