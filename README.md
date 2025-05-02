# Image Crop Tool

A responsive web application built with Laravel and Cropper.js that allows users to upload, crop, preview, and download images with customizable aspect ratios and formats.

---

## ✨ Features

- ✅ Upload and preview image before cropping  
- ✅ Crop image client-side using [Cropper.js](https://github.com/fengyuanchen/cropperjs)  
- ✅ Choose aspect ratio (1:1, 16:9, 9:16, or free)  
- ✅ Select download format (PNG or JPG)  
- ✅ Upload and store cropped image via Laravel backend  
- ✅ Download cropped result directly  
- ✅ Reset and re-crop functionality  
- ✅ Toast notifications for success and error  
- ✅ Loading spinner during upload  
- ✅ Organized Blade components (`<x-header>`, `<x-footer>`, `<x-toast>`)  
- ✅ Fully responsive UI using Tailwind CSS  

---

## 🛠️ Tech Stack

- Laravel 12  
- Cropper.js  
- Tailwind CSS  
- Blade components  
- Vanilla JavaScript  

---

## 🚀 Getting Started

Follow the steps below to set up and run the project locally:

```bash
1. Clone the repository:
   git clone https://github.com/onyiimesi/image-cropper.git
   cd image-cropper

2. Install dependencies:
   composer install

3. Set up environment:
   cp .env.example .env
   php artisan key:generate

4. Link the storage folder:
   php artisan storage:link

5. Start the development server:
   php artisan serve
