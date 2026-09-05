const THUMBNAIL_MAX_EDGE = 480;
const THUMBNAIL_QUALITY = 0.72;
const IMAGE_MAX_EDGE = 2560;
const IMAGE_MAX_BYTES = Math.floor(1.8 * 1024 * 1024);
const IMAGE_MIN_EDGE = 640;
const IMAGE_RESIZE_FACTOR = 0.8;
const IMAGE_QUALITIES = [0.86, 0.78, 0.7, 0.62, 0.54, 0.46];
const COMPRESSIBLE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/bmp'];
const HEIC_MIME_TYPES = ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'];

const isHeic = (file) => HEIC_MIME_TYPES.includes(file.type) || /\.hei[cf]$/i.test(file.name);

const loadImage = async (file) => {
  const objectUrl = URL.createObjectURL(file);

  try {
    const image = new Image();
    image.src = objectUrl;
    await image.decode();
    return image;
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
};

const createCanvas = (image, maxEdge) => {
  const scale = Math.min(1, maxEdge / Math.max(image.naturalWidth, image.naturalHeight));
  const canvas = document.createElement('canvas');
  canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
  canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));

  const context = canvas.getContext('2d');
  if (!context) throw new Error('Не вдалося підготувати зображення до завантаження.');

  context.drawImage(image, 0, 0, canvas.width, canvas.height);

  return canvas;
};

const canvasToWebp = (canvas, quality) => new Promise((resolve, reject) => {
  canvas.toBlob((blob) => {
    if (blob?.type === 'image/webp') {
      resolve(blob);
    } else {
      reject(new Error('Ваш браузер не підтримує оптимізацію зображень у WebP.'));
    }
  }, 'image/webp', quality);
});

const webpFile = (blob, originalName, suffix = '') => {
  const basename = originalName.replace(/\.[^.]+$/, '') || 'image';

  return new File([blob], `${basename}${suffix}.webp`, {
    type: 'image/webp',
    lastModified: Date.now(),
  });
};

export const compressImage = async (file, mediaType) => {
  if (mediaType !== 'image' || !COMPRESSIBLE_MIME_TYPES.includes(file.type)) {
    if (mediaType === 'image' && file.size > IMAGE_MAX_BYTES && !isHeic(file)) {
      throw new Error('Цей формат неможливо автоматично стиснути. Оберіть файл розміром до 1.8 МБ.');
    }

    return file;
  }

  const image = await loadImage(file);
  const originalMaxEdge = Math.max(image.naturalWidth, image.naturalHeight);

  if (file.size <= IMAGE_MAX_BYTES && originalMaxEdge <= IMAGE_MAX_EDGE) {
    return file;
  }

  let maxEdge = Math.min(IMAGE_MAX_EDGE, originalMaxEdge);
  const minEdge = Math.min(IMAGE_MIN_EDGE, maxEdge);
  const targetBytes = Math.min(IMAGE_MAX_BYTES, file.size);

  while (true) {
    const canvas = createCanvas(image, maxEdge);

    for (const quality of IMAGE_QUALITIES) {
      const blob = await canvasToWebp(canvas, quality);
      if (blob.size <= targetBytes) return webpFile(blob, file.name);
    }

    if (maxEdge === minEdge) break;
    maxEdge = Math.max(minEdge, Math.floor(maxEdge * IMAGE_RESIZE_FACTOR));
  }

  if (file.size <= IMAGE_MAX_BYTES) return file;

  throw new Error('Не вдалося стиснути зображення до 1.8 МБ.');
};

export const createThumbnail = async (file, mediaType) => {
  if (mediaType !== 'image' || !COMPRESSIBLE_MIME_TYPES.includes(file.type)) {
    return null;
  }

  const image = await loadImage(file);
  const canvas = createCanvas(image, THUMBNAIL_MAX_EDGE);
  const blob = await canvasToWebp(canvas, THUMBNAIL_QUALITY);

  return webpFile(blob, file.name, '.preview');
};
