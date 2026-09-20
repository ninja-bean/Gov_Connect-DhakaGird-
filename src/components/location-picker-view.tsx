"use client";

import { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

export default function LocationPickerView({
  initialLat,
  initialLng,
}: {
  initialLat?: number | null;
  initialLng?: number | null;
}) {
  const [showMap, setShowMap] = useState(false);
  const mapRef = useRef<L.Map | null>(null);
  const markerRef = useRef<L.Marker | null>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const latInputRef = useRef<HTMLInputElement>(null);
  const lngInputRef = useRef<HTMLInputElement>(null);
  const nameInputRef = useRef<HTMLInputElement>(null);

  async function setCoords(lat: number, lng: number) {
    if (latInputRef.current) latInputRef.current.value = String(lat);
    if (lngInputRef.current) lngInputRef.current.value = String(lng);
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`,
      );
      const data = (await res.json()) as { display_name?: string };
      if (nameInputRef.current) {
        nameInputRef.current.value = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
      }
    } catch {
      if (nameInputRef.current) {
        nameInputRef.current.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
      }
    }
  }

  function useCurrentLocation() {
    if (!navigator.geolocation) {
      alert("Geolocation is not supported by your browser");
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => void setCoords(pos.coords.latitude, pos.coords.longitude),
      () => alert("Unable to get your location. Please enable location services."),
    );
  }

  function toggleMap() {
    if (showMap) {
      setShowMap(false);
      return;
    }
    setShowMap(true);
    if (mapRef.current) {
      setTimeout(() => mapRef.current?.invalidateSize(), 100);
      return;
    }
    const el = containerRef.current;
    if (!el) return;
    const map = L.map(el, {
      center: [23.78, 90.4],
      zoom: 12,
      minZoom: 11,
      maxBounds: L.latLngBounds(L.latLng(23.65, 90.3), L.latLng(23.9, 90.55)),
    });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    if (initialLat && initialLng) {
      map.setView([initialLat, initialLng], 13);
      markerRef.current = L.marker([initialLat, initialLng]).addTo(map);
    }

    map.on("click", (e: L.LeafletMouseEvent) => {
      markerRef.current?.remove();
      markerRef.current = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
      void setCoords(e.latlng.lat, e.latlng.lng);
    });

    mapRef.current = map;
  }

  useEffect(() => {
    return () => {
      mapRef.current?.remove();
      mapRef.current = null;
    };
  }, []);

  return (
    <div className="space-y-3">
      <input
        ref={nameInputRef}
        type="text"
        name="location"
        readOnly
        placeholder="Click a button below to set location"
        className="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
      />
      <input ref={latInputRef} type="hidden" name="latitude" defaultValue={initialLat ?? ""} />
      <input ref={lngInputRef} type="hidden" name="longitude" defaultValue={initialLng ?? ""} />
      <div className="flex flex-wrap gap-3">
        <button
          type="button"
          onClick={useCurrentLocation}
          className="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-red-500 hover:text-red-600"
        >
          Use current location
        </button>
        <button
          type="button"
          onClick={toggleMap}
          className="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-red-500 hover:text-red-600"
        >
          {showMap ? "Hide map" : "Pick on map"}
        </button>
      </div>
      {showMap && <div ref={containerRef} className="h-[360px] w-full rounded-xl border border-slate-200" />}
    </div>
  );
}