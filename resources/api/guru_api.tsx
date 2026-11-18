import axios from "axios";

const API_URL = "/api/guru";

/**
 * Ambil semua guru
 * (opsional: { status: 'aktif', search: 'matematika' })
 */
export const getAllGuru = async (params?: any) => {
  const response = await axios.get(API_URL, { params });
  return response.data.data || response.data;
};

/**
 * Ambil guru by id (beserta kelas yang diwalikan)
 */
export const getGuruById = async (id: number) => {
  const response = await axios.get(`${API_URL}/${id}`);
  return response.data.data || response.data;
};

/**
 * Create guru
 */
export const createGuru = async (data: any) => {
  const response = await axios.post(API_URL, data);
  return response.data;
};

/**
 * Update guru
 */
export const updateGuru = async (id: number, data: any) => {
  const response = await axios.put(`${API_URL}/${id}`, data);
  return response.data;
};

/**
 * Update status guru (aktif / nonaktif)
 */
export const updateStatusGuru = async (id: number, status: string) => {
  const response = await axios.put(`${API_URL}/${id}`, { status });
  return response.data;
};

/**
 * Delete guru
 */
export const deleteGuru = async (id: number) => {
  const response = await axios.delete(`${API_URL}/${id}`);
  return response.data;
};