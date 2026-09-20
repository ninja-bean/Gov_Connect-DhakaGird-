"use client";

import dynamic from "next/dynamic";

export type MapMarker = {
  lat: number;
  lng: number;
  category: string;
  status: string;
  createdAt: string;
};

const ProblemMapView = dynamic(() => import("./problem-map-view"), {
  ssr: false,
});

export default function ProblemMap({
  markers,
  height = 420,
}: {
  markers: MapMarker[];
  height?: number;
}) {
  return <ProblemMapView markers={markers} height={height} />;
}