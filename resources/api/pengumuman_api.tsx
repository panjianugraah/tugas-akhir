import axios from "axios";

const API_URL = "/api/pengumuman";

// Ubah object biasa menjadi FormData (untuk upload gambar)
const toFormData = (data: any) => {
  const form = new FormData();
  Object.entries(data).forEach(([key, value]) => {
    if (value === undefined || value === null) return;
    if (typeof value === "boolean") form.append(key, value ? "1" : "0");
    else form.append(key, value as any);
  });
  return form;
};

/**
 * Ambil semua pengumuman
 * (opsional: { status: 'terbit', target: 'Siswa', search: 'rapat', aktif: 1 })
 */
export const getAllPengumuman = async (params?: any) => {
  const response = await axios.get(API_URL, { params });
  return response.data.data || response.data;
};

/**
 * Ambil pengumuman by id
 */
export const getPengumumanById = async (id: number) => {
  const response = await axios.get(`${API_URL}/${id}`);
  return response.data.data || response.data;
};

/**
 * Ambil pengumuman by slug (halaman baca)
 */
export const getPengumumanBySlug = async (slug: string) => {
  const response = await axios.get(`${API_URL}/slug/${slug}`);
  return response.data.data || response.data;
};

/**
 * Create pengumuman
 * data: { judul, isi, target, berlaku_sampai, status, gambar: File }
 */
export const createPengumuman = async (data: any) => {
  const response = await axios.post(API_URL, toFormData(data));
  return response.data;
};

/**
 * Update pengumuman (boleh dengan gambar baru / hapus_gambar: true)
 */
export const updatePengumuman = async (id: number, data: any) => {
  const response = await axios.post(`${API_URL}/${id}`, toFormData({ ...data, _method: "PUT" }));
  return response.data;
};

/**
 * Update status (draft / terbit)
 */
export const updateStatusPengumuman = async (id: number, status: string) => {
  const response = await axios.put(`${API_URL}/${id}`, { status });
  return response.data;
};

/**
 * Delete pengumuman
 */
export const deletePengumuman = async (id: number) => {
  const response = await axios.delete(`${API_URL}/${id}`);
  return response.data;
};

/**
 * Ambil pengumuman publik:
 * terbit, target Umum, dan belum kedaluwarsa.
 */
export const getPengumumanPublik = async () => {
    const response = await axios.get(`${API_URL}/publik`);
    return response.data.data || response.data;
};