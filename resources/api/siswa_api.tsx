import axios from "axios";

const API_URL = "/api/siswa";

/**
 * Ambil semua siswa
 * (opsional: { status: 'aktif', kelas_id: 1, search: 'andi' })
 */
export const getAllSiswa = async (params?: any) => {
  const response = await axios.get(API_URL, { params });
  return response.data.data || response.data;
};

/**
 * Ambil siswa by id (beserta data kelas & wali kelas)
 */
export const getSiswaById = async (id: number) => {
  const response = await axios.get(`${API_URL}/${id}`);
  return response.data.data || response.data;
};

/**
 * Create siswa
 */
export const createSiswa = async (data: any) => {
  const response = await axios.post(API_URL, data);
  return response.data;
};

/**
 * Update siswa
 */
export const updateSiswa = async (id: number, data: any) => {
  const response = await axios.put(`${API_URL}/${id}`, data);
  return response.data;
};

/**
 * Update status siswa (aktif / lulus / pindah / keluar)
 */
export const updateStatusSiswa = async (id: number, status: string) => {
  const response = await axios.put(`${API_URL}/${id}`, { status });
  return response.data;
};

/**
 * Pindah kelas siswa
 */
export const pindahKelasSiswa = async (id: number, kelas_id: number | null) => {
  const response = await axios.put(`${API_URL}/${id}`, { kelas_id });
  return response.data;
};

/**
 * Delete siswa
 */
export const deleteSiswa = async (id: number) => {
  const response = await axios.delete(`${API_URL}/${id}`);
  return response.data;
};