import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/services/api_service.dart';

class AddProductScreen extends ConsumerStatefulWidget {
  const AddProductScreen({super.key});

  @override
  ConsumerState<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends ConsumerState<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _categoryController = TextEditingController();
  bool _submitting = false;
  XFile? _image;

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1200, imageQuality: 85);
    if (picked != null) {
      setState(() => _image = picked);
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);

    try {
      final name = _nameController.text.trim();
      final description = _descriptionController.text.trim();
      final price = double.parse(_priceController.text.trim());
      final category = _categoryController.text.trim();

      final payload = {
        'name': name,
        'description': description,
        'price': price,
        'category': category.isEmpty ? 'General' : category,
      };

      final res = await apiService.createProduct(payload);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Product created')));
      Navigator.of(context).pop(res);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Add Product'),
        backgroundColor: Colors.white,
        elevation: 1,
      ),
      backgroundColor: AppColors.backgroundSecondary,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _textField(
                controller: _nameController,
                label: 'Product Name',
                validator: (v) => v == null || v.trim().isEmpty ? 'Name is required' : null,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _descriptionController,
                label: 'Description',
                maxLines: 4,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _priceController,
                label: 'Price',
                keyboardType: TextInputType.number,
                validator: (v) {
                  final t = v?.trim();
                  if (t == null || t.isEmpty) return 'Price is required';
                  if (double.tryParse(t) == null) return 'Enter a valid number';
                  return null;
                },
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _categoryController,
                label: 'Category (optional)',
              ),
              const SizedBox(height: 16.0),
              Text('Image (optional)', style: AppTypography.titleSmall),
              const SizedBox(height: 8.0),
              Row(
                children: [
                  ElevatedButton.icon(
                    onPressed: _submitting ? null : _pickImage,
                    icon: const Icon(Icons.photo),
                    label: const Text('Choose Image'),
                  ),
                  const SizedBox(width: 12.0),
                  if (_image != null)
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.file(File(_image!.path), height: 80, fit: BoxFit.cover),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 24.0),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _submitting ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary500,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                  child: _submitting
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Create Product'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _textField({
    required TextEditingController controller,
    required String label,
    String? Function(String?)? validator,
    int maxLines = 1,
    TextInputType? keyboardType,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      validator: validator,
    );
  }
}
