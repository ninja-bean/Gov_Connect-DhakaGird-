import { afterEach, describe, expect, it, vi } from 'vitest'
import {
  DHAKA_BOUNDS,
  inDhaka,
  isCurrentlyBanned,
  resolveLocation,
  reverseGeocode,
} from './problems'

afterEach(() => {
  vi.unstubAllGlobals()
  vi.restoreAllMocks()
})

describe('inDhaka', () => {
  it('accepts coordinates inside the Dhaka bounding box', () => {
    expect(inDhaka(23.8, 90.4)).toBe(true)
    expect(inDhaka(DHAKA_BOUNDS.minLat, DHAKA_BOUNDS.minLng)).toBe(true)
    expect(inDhaka(DHAKA_BOUNDS.maxLat, DHAKA_BOUNDS.maxLng)).toBe(true)
  })

  it('rejects coordinates outside the Dhaka bounding box', () => {
    expect(inDhaka(22.85, 91.4)).toBe(false) // Chittagong
    expect(inDhaka(23.9, 90.6)).toBe(false)
    expect(inDhaka(DHAKA_BOUNDS.maxLat + 0.1, 90.4)).toBe(false)
  })
})

describe('isCurrentlyBanned', () => {
  it('bans a user flagged as banned regardless of expiry', () => {
    expect(isCurrentlyBanned({ is_banned: true, ban_until: null })).toBe(true)
    expect(
      isCurrentlyBanned({ is_banned: true, ban_until: '2020-01-01 00:00:00' }),
    ).toBe(true)
  })

  it('treats "permanent" as an indefinite ban', () => {
    expect(
      isCurrentlyBanned({ is_banned: false, ban_until: 'permanent' }),
    ).toBe(true)
    expect(
      isCurrentlyBanned({ is_banned: false, ban_until: 'PERMANENT' }),
    ).toBe(true)
  })

  it('honours a future expiry date', () => {
    expect(
      isCurrentlyBanned({
        is_banned: false,
        ban_until: new Date(Date.now() + 60_000).toISOString(),
      }),
    ).toBe(true)
  })

  it('lifts the ban once the expiry passes', () => {
    expect(
      isCurrentlyBanned({
        is_banned: false,
        ban_until: new Date(Date.now() - 60_000).toISOString(),
      }),
    ).toBe(false)
  })

  it('ignores malformed dates', () => {
    expect(isCurrentlyBanned({ is_banned: false, ban_until: 'not-a-date' })).toBe(
      false,
    )
    expect(isCurrentlyBanned({ is_banned: false, ban_until: null })).toBe(false)
  })
})

describe('resolveLocation', () => {
  it('prefers a trimmed address when present', () => {
    expect(resolveLocation('  Dhanmondi 27  ', 23.81, 90.37)).toBe('Dhanmondi 27')
  })

  it('falls back to formatted coordinates for missing addresses', () => {
    expect(resolveLocation(null, 23.8103, 90.4125)).toBe('23.810300, 90.412500')
    expect(resolveLocation('', 23.8103, 90.4125)).toBe('23.810300, 90.412500')
    expect(resolveLocation('   ', 1.5, 2.25)).toBe('1.500000, 2.250000')
  })
})

describe('reverseGeocode', () => {
  it('returns the address from Nominatim', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({ display_name: 'Dhanmondi, Dhaka, Bangladesh' }),
          { status: 200 },
        ),
      ),
    )
    await expect(reverseGeocode(23.75, 90.375)).resolves.toBe(
      'Dhanmondi, Dhaka, Bangladesh',
    )
    const fetchMock = vi.mocked(fetch)
    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(fetchMock.mock.calls[0][0]).toContain('https://nominatim.openstreetmap.org/reverse')
  })

  it('returns null on non-OK responses', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response(null, { status: 429 })))
    await expect(reverseGeocode(23.75, 90.375)).resolves.toBeNull()
  })

  it('returns null on network failure or timeout', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockRejectedValue(new Error('socket hang up')),
    )
    await expect(reverseGeocode(23.75, 90.375)).resolves.toBeNull()
  })
})