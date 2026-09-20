"use client";

import { useEffect, useRef } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import type { MapMarker } from "./problem-map";

export default function ProblemMapView({ markers, height }: { markers: MapMarker[]; height: number }) {
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const el = containerRef.current;
    if (!el) return;

    const map = L.map(el, {
      center: [23.7806, 90.4193],
      zoom: 12,
      minZoom: 11,
      maxBounds: L.latLngBounds(L.latLng(23.65, 90.3), L.latLng(23.9, 90.55)),
    });

    L.tileLayer("https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", {
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    for (const m of markers) {
      const isSos = m.category.toUpperCase() === "SOS";
      L.circleMarker([m.lat, m.lng], {
        radius: 8,
        fillColor: isSos ? "#ef4444" : "#0f172a",
        color: "#ffffff",
        weight: 2,
        fillOpacity: 0.9,
      })
        .addTo(map)
        .bindPopup(
          `<strong>${m.category.toUpperCase()}</strong><br><small>${new Date(
            m.createdAt,
          ).toLocaleString()}</small>`,
        );
    }

    return () => {
      map.remove();
    };
  }, [markers]);

  return <div ref={containerRef} style={{ height }} className="z-0 w-full rounded-xl" />;
}