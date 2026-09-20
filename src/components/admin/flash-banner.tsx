import { Alert } from "@/components/ui/alert";

export default function FlashBanner({
  ok,
  err,
}: {
  ok?: string | string[];
  err?: string | string[];
}) {
  const okMsg = Array.isArray(ok) ? ok.join(" ") : ok;
  const errMsg = Array.isArray(err) ? err.join(" ") : err;
  return (
    <>
      {okMsg && <Alert tone="success">{okMsg}</Alert>}
      {errMsg && <Alert tone="error">{errMsg}</Alert>}
    </>
  );
}