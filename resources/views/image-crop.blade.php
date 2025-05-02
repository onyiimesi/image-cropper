@extends('components.layout')

@section('content')
  <div class="bg-white rounded-xl shadow-md p-6 space-y-6">
    <!-- Upload Controls -->
    <div class="space-y-4">
      <input
        type="file"
        id="imageInput"
        accept="image/*"
        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:outline-none"
      />

      <!-- Aspect Ratio Selector -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Aspect Ratio:</label>
        <select id="aspectRatioSelect" class="w-full border rounded px-3 py-2">
          <option value="1">Square (1:1)</option>
          <option value="1.7778">Landscape (16:9)</option>
          <option value="0.5625">Portrait (9:16)</option>
          <option value="NaN">Free</option>
        </select>
      </div>

      <!-- Download Format Selector -->
      <div class="flex items-center gap-4">
        <label class="text-sm text-gray-700">Download as:</label>
        <select id="downloadFormat" class="border rounded px-2 py-1 text-sm">
          <option value="png">PNG</option>
          <option value="jpeg">JPG</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col sm:flex-row gap-4">
        <button
          id="cropButton"
          class="flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition w-full sm:w-auto"
        >
          <svg id="loader" class="hidden w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
            </path>
          </svg>
          <span id="cropButtonText">Crop & Upload</span>
        </button>

        <button
          id="resetButton"
          class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition w-full sm:w-auto"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Crop & Preview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Crop Area -->
      <div>
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Crop Image</h2>
        <div class="border rounded bg-gray-50 p-2 h-[400px] overflow-hidden">
          <img id="image" class="max-h-full object-contain" alt="" />
        </div>
      </div>

      <!-- Cropped Result -->
      <div>
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Cropped Result</h2>
        <div class="border rounded bg-gray-50 p-2 flex flex-col items-center justify-center h-[400px]">
          <img id="preview" class="max-h-full object-contain border border-gray-300 rounded mb-4" alt="" />
          <a id="downloadBtn"
             class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition hidden"
             download
             target="_blank">
             Download Cropped Image
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  let cropper;
  let aspectRatio = 1;

  const imageInput = document.getElementById('imageInput');
  const aspectRatioSelect = document.getElementById('aspectRatioSelect');
  const image = document.getElementById('image');
  const preview = document.getElementById('preview');
  const downloadBtn = document.getElementById('downloadBtn');
  const cropButton = document.getElementById('cropButton');
  const cropButtonText = document.getElementById('cropButtonText');
  const loader = document.getElementById('loader');

  function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `fixed top-5 right-5 z-50 px-4 py-2 rounded shadow text-white text-sm transition-all duration-300 ${
      type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.classList.remove('hidden');
    setTimeout(() => {
      toast.classList.add('hidden');
    }, 3000);
  }

  aspectRatioSelect.addEventListener('change', (e) => {
    const value = parseFloat(e.target.value);
    aspectRatio = isNaN(value) ? NaN : value;
    if (cropper) {
      cropper.setAspectRatio(aspectRatio);
    }
  });

  imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      showToast('Only image files are allowed.', 'error');
      imageInput.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      image.onload = () => {
        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
          aspectRatio: aspectRatio,
          viewMode: 1
        });
      };
      image.src = reader.result;
    };
    reader.readAsDataURL(file);
  });

  cropButton.addEventListener('click', () => {
    if (!cropper) {
      showToast('Please upload an image before cropping.', 'error');
      return;
    }

    const canvas = cropper.getCroppedCanvas();
    preview.src = canvas.toDataURL();

    canvas.toBlob((blob) => {
      const formData = new FormData();
      formData.append('cropped_image', blob);
      formData.append('_token', '{{ csrf_token() }}');

      cropButton.disabled = true;
      cropButton.classList.add('opacity-60', 'cursor-not-allowed');
      loader.classList.remove('hidden');
      cropButtonText.textContent = 'Uploading...';

      fetch('{{ route("crop.upload") }}', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          cropButton.disabled = false;
          loader.classList.add('hidden');
          cropButton.classList.remove('opacity-60', 'cursor-not-allowed');
          cropButtonText.textContent = 'Crop & Upload';

          if (data.path) {
            const selectedFormat = document.getElementById('downloadFormat').value;
            const extension = selectedFormat === 'jpeg' ? 'jpg' : 'png';
            const finalUrl = `${window.location.origin}${data.path}?t=${Date.now()}`;
            preview.src = finalUrl;

            downloadBtn.href = finalUrl;
            downloadBtn.download = `cropped-image.${extension}`;
            downloadBtn.classList.remove('hidden');

            showToast('Cropped image uploaded! ✅', 'success');
          } else {
            showToast('Upload failed. Please try again.', 'error');
          }
        })
        .catch(() => {
          cropButton.disabled = false;
          loader.classList.add('hidden');
          cropButton.classList.remove('opacity-60', 'cursor-not-allowed');
          cropButtonText.textContent = 'Crop & Upload';
          showToast('Something went wrong.', 'error');
        });
    }, 'image/png');
  });

  document.getElementById('resetButton').addEventListener('click', () => {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    image.src = '';
    preview.src = '';
    imageInput.value = '';
    downloadBtn.classList.add('hidden');
    aspectRatioSelect.value = '1';
    aspectRatio = 1;
    cropButtonText.textContent = 'Crop & Upload';
  });
</script>
@endsection
