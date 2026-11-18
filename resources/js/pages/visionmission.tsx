import { Eye, Target } from 'lucide-react';

const vision =
  'Terwujudnya Sekolah Angkasa yang unggul dan rujukan dalam menghasilkan abdi yang beragama, berbangsa dan bernegara serta berilmu demi kejayaan Nusantara.';

const missions: string[] = [
  'Menyelenggarakan pendidikan dan pembelajaran agama guna menghasilkan lulusan yang beriman dan bertakwa terhadap Tuhan Yang Maha Esa dan berakhlak mulia.',
  'Menyelenggarakan pendidikan dan pembelajaran kebangsaan guna menghasilkan lulusan yang cinta tanah air, cinta alam sekitar, cinta sesama dan cinta diri sendiri.',
  'Menyelenggarakan pendidikan dan pembelajaran ilmu pengetahuan dan teknologi guna menghasilkan lulusan yang cerdas intelektual, kinestetis, dan estetis, cinta ilmu pengetahuan, teknologi dan keunggulan sesuai minat dan bakat peserta didik.',
];

export default function VisionMission() {
  return (
    <section id="vision" className="py-16 bg-gradient-to-br from-blue-50 to-indigo-50 scroll-mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold text-gray-900 mb-4">Visi & Misi</h2>
          <p className="text-xl text-gray-600">Arah dan tujuan pendidikan SMP Angkasa Adi Soemarmo</p>
        </div>

        <div className="grid md:grid-cols-2 gap-8">
          {/* Visi */}
          <div className="bg-white p-8 rounded-lg shadow-lg flex flex-col">
            <div className="flex items-center">
              <div className="bg-blue-100 p-3 rounded-full mr-4">
                <Eye className="w-8 h-8 text-blue-600" />
              </div>
              <h3 className="text-3xl font-bold text-gray-900">Visi</h3>
            </div>
            <div className="flex-1 flex items-center justify-center">
              <blockquote className="text-center text-gray-700 text-xl leading-relaxed italic max-w-md">
                “{vision}”
              </blockquote>
            </div>
          </div>

          {/* Misi */}
          <div className="bg-white p-8 rounded-lg shadow-lg">
            <div className="flex items-center mb-6">
              <div className="bg-indigo-100 p-3 rounded-full mr-4">
                <Target className="w-8 h-8 text-indigo-600" />
              </div>
              <h3 className="text-3xl font-bold text-gray-900">Misi</h3>
            </div>
            <ol className="space-y-4">
              {missions.map((mission, index) => (
                <li key={index} className="flex gap-3">
                  <span className="flex-shrink-0 w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center">
                    {index + 1}
                  </span>
                  <p className="text-gray-700 leading-relaxed">{mission}</p>
                </li>
              ))}
            </ol>
          </div>
        </div>
      </div>
    </section>
  );
}