import 'package:flutter/material.dart';
import '../models/marketplace_models.dart';

class FilterSheet extends StatefulWidget {
  final BusinessFilters currentFilters;

  const FilterSheet({
    super.key,
    required this.currentFilters,
  });

  @override
  State<FilterSheet> createState() => _FilterSheetState();
}

class _FilterSheetState extends State<FilterSheet> {
  late BusinessFilters _filters;
  final RangeValues _valuationRange = const RangeValues(0, 100000000);
  final RangeValues _fundingRange = const RangeValues(0, 10000000);
  
  RangeValues _selectedValuationRange = const RangeValues(0, 100000000);
  RangeValues _selectedFundingRange = const RangeValues(0, 10000000);

  @override
  void initState() {
    super.initState();
    _filters = widget.currentFilters;
    
    // Initialize range values from current filters
    _selectedValuationRange = RangeValues(
      widget.currentFilters.minValuation ?? 0,
      widget.currentFilters.maxValuation ?? 100000000,
    );
    
    _selectedFundingRange = RangeValues(
      widget.currentFilters.minFunding ?? 0,
      widget.currentFilters.maxFunding ?? 10000000,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.8,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(20),
        ),
      ),
      child: Column(
        children: [
          // Handle
          Container(
            width: 40,
            height: 4,
            margin: const EdgeInsets.only(top: 12),
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                const Expanded(
                  child: Text(
                    'Filter Businesses',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: _clearFilters,
                  child: const Text('Clear All'),
                ),
              ],
            ),
          ),

          const Divider(height: 1),

          // Content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Industry Filter
                  _buildSectionTitle('Industry'),
                  _buildIndustryFilter(),

                  const SizedBox(height: 24),

                  // Valuation Range
                  _buildSectionTitle('Valuation Range'),
                  _buildValuationRange(),

                  const SizedBox(height: 24),

                  // Funding Goal Range
                  _buildSectionTitle('Funding Goal Range'),
                  _buildFundingRange(),

                  const SizedBox(height: 24),

                  // Location Filter
                  _buildSectionTitle('Location'),
                  _buildLocationFilter(),

                  const SizedBox(height: 24),

                  // Sort Options
                  _buildSectionTitle('Sort By'),
                  _buildSortOptions(),
                ],
              ),
            ),
          ),

          // Actions
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(
                top: BorderSide(color: Colors.grey[200]!),
              ),
            ),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel'),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _applyFilters,
                    child: const Text('Apply Filters'),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _buildIndustryFilter() {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: Industry.values.map((industry) {
        final isSelected = _filters.industry == industry.name;
        return FilterChip(
          label: Text(industry.displayName),
          selected: isSelected,
          onSelected: (selected) {
            setState(() {
              _filters = _filters.copyWith(
                industry: selected ? industry.name : null,
              );
            });
          },
        );
      }).toList(),
    );
  }

  Widget _buildValuationRange() {
    return Column(
      children: [
        RangeSlider(
          values: _selectedValuationRange,
          min: _valuationRange.start,
          max: _valuationRange.end,
          divisions: 20,
          labels: RangeLabels(
            '\$${(_selectedValuationRange.start / 1000000).toStringAsFixed(1)}M',
            '\$${(_selectedValuationRange.end / 1000000).toStringAsFixed(1)}M',
          ),
          onChanged: (values) {
            setState(() {
              _selectedValuationRange = values;
              _filters = _filters.copyWith(
                minValuation: values.start,
                maxValuation: values.end,
              );
            });
          },
        ),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              '\$${(_selectedValuationRange.start / 1000000).toStringAsFixed(1)}M',
              style: const TextStyle(fontSize: 12),
            ),
            Text(
              '\$${(_selectedValuationRange.end / 1000000).toStringAsFixed(1)}M',
              style: const TextStyle(fontSize: 12),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildFundingRange() {
    return Column(
      children: [
        RangeSlider(
          values: _selectedFundingRange,
          min: _fundingRange.start,
          max: _fundingRange.end,
          divisions: 20,
          labels: RangeLabels(
            '\$${(_selectedFundingRange.start / 1000000).toStringAsFixed(1)}M',
            '\$${(_selectedFundingRange.end / 1000000).toStringAsFixed(1)}M',
          ),
          onChanged: (values) {
            setState(() {
              _selectedFundingRange = values;
              _filters = _filters.copyWith(
                minFunding: values.start,
                maxFunding: values.end,
              );
            });
          },
        ),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              '\$${(_selectedFundingRange.start / 1000000).toStringAsFixed(1)}M',
              style: const TextStyle(fontSize: 12),
            ),
            Text(
              '\$${(_selectedFundingRange.end / 1000000).toStringAsFixed(1)}M',
              style: const TextStyle(fontSize: 12),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildLocationFilter() {
    return TextField(
      decoration: const InputDecoration(
        hintText: 'Enter city or state...',
        border: OutlineInputBorder(),
        prefixIcon: Icon(Icons.location_on),
      ),
      onChanged: (value) {
        setState(() {
          _filters = _filters.copyWith(
            location: value.isEmpty ? null : value,
          );
        });
      },
    );
  }

  Widget _buildSortOptions() {
    final sortOptions = [
      {'value': 'created_at', 'label': 'Newest First'},
      {'value': 'valuation', 'label': 'Highest Valuation'},
      {'value': 'funding_goal', 'label': 'Highest Funding Goal'},
      {'value': 'views_count', 'label': 'Most Popular'},
    ];

    return Column(
      children: sortOptions.map((option) {
        return RadioListTile<String>(
          title: Text(option['label']!),
          value: option['value']!,
          groupValue: _filters.sortBy,
          onChanged: (value) {
            setState(() {
              _filters = _filters.copyWith(sortBy: value ?? 'created_at');
            });
          },
        );
      }).toList(),
    );
  }

  void _clearFilters() {
    setState(() {
      _filters = const BusinessFilters();
      _selectedValuationRange = _valuationRange;
      _selectedFundingRange = _fundingRange;
    });
  }

  void _applyFilters() {
    Navigator.of(context).pop(_filters);
  }
}
