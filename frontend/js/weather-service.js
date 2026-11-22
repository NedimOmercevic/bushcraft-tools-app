export async function getWeather(city) {
  const url = new URL("../../backend/weather", import.meta.url);
  url.searchParams.set("city", city);
  const response = await fetch(url, { headers: { Accept: "application/json" } });
  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.error || "Weather is temporarily unavailable.");
  }
  return data;
}
