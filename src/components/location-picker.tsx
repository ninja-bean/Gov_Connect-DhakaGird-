"use client";

import dynamic from "next/dynamic";

const LocationPickerView = dynamic(() => import("./location-picker-view"), {
  ssr: false,
});

export default function LocationPicker({
  initialLat,
  initialLng,
}: {
  initialLat?: number | null;
  initialLng?: number | null;
}) {
  return <LocationPickerView initialLat={initialLat} initialLng={initialLng} />;
}