import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { AnalyticsDoughnut } from './analytics-doughnut'

describe('AnalyticsDoughnut', () => {
  it('renders every slice with its label and value', () => {
    render(
      <AnalyticsDoughnut
        slices={[
          { label: 'Traffic', value: 12, color: '#1E293B' },
          { label: 'Water', value: 4, color: '#3B82F6' },
        ]}
      />,
    )
    expect(screen.getByText('Traffic')).toBeDefined()
    expect(screen.getByText('12')).toBeDefined()
    expect(screen.getByText('Water')).toBeDefined()
    expect(screen.getByText('4')).toBeDefined()
    expect(
      screen.getByRole('img', {
        name: 'Distribution of your reports by category',
      }),
    ).toBeDefined()
  })

  it('omits zero-valued slices from the chart but lists them', () => {
    render(
      <AnalyticsDoughnut
        slices={[
          { label: 'SOS', value: 0, color: '#EF4444' },
          { label: 'Fire', value: 0, color: '#F97316' },
        ]}
      />,
    )
    const svg = screen
      .getByRole('img', { name: 'Distribution of your reports by category' })
      .querySelectorAll('circle')
    expect(svg.length).toBe(2) // background ring + empty-state placeholder
    expect(screen.getByText('SOS')).toBeDefined()
    expect(screen.getByText('Fire')).toBeDefined()
  })
})