import 'package:flutter/material.dart';

class SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final Function(String) onSearch;
  final String hintText;
  final bool enabled;

  const SearchBar({
    super.key,
    required this.controller,
    required this.onSearch,
    this.hintText = 'Search...',
    this.enabled = true,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[300]!),
      ),
      child: TextField(
        controller: controller,
        enabled: enabled,
        onSubmitted: onSearch,
        decoration: InputDecoration(
          hintText: hintText,
          hintStyle: TextStyle(color: Colors.grey[500]),
          prefixIcon: Icon(
            Icons.search,
            color: Colors.grey[500],
          ),
          suffixIcon: controller.text.isNotEmpty
              ? IconButton(
                  icon: Icon(
                    Icons.clear,
                    color: Colors.grey[500],
                  ),
                  onPressed: () {
                    controller.clear();
                    onSearch('');
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 12,
          ),
        ),
      ),
    );
  }
}
