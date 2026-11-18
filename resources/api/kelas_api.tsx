import axios from "axios";

const API_URL = "/api/kelas";

/**
 * Ambil semua kelas
 * (opsional: { tingkat: 10, tahun_ajaran: '2026/2027', search: 'IPA' })
 */
export const getAllKelas = async (params?: any) => {
  const response = await axios.get(API_URL, { params });
  return response.data.data || response.data;
};

/**
 * Ambil kelas by id (beserta wali kelas & daftar siswa)
 */
export const getKelasById = async (id: number) => {
  const response = await axios.get(`${API_URL}/${id}`);
  return response.data.data || response.data;
};

/**
 * Create kelas
 */
export const createKelas = async (data: any) => {
  const response = await axios.post(API_URL, data);
  return response.data;
};

/**
 * Update kelas
 */
export const updateKelas = async (id: number, data: any) => {
  const response = await axios.put(`${API_URL}/${id}`, data);
  return response.data;
};

/**
 * Ganti wali kelas
 */
export const updateWaliKelas = async (id: number, wali_kelas_id: number | null) => {
  const response = await axios.put(`${API_URL}/${id}`, { wali_kelas_id });
  return response.data;
};

/**
 * Delete kelas
 */
export const deleteKelas = async (id: number) => {
  const response = await axios.delete(`${API_URL}/${id}`);
  return response.data;
};