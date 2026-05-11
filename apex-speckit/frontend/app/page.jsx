import Navbar from '@/components/Navbar';

export default function Home() {
  return (
    <>
      <Navbar />
      <main className="home">
        <section className="hero">
          <h1>Apex Leadership Summit 2026</h1>
          <p>Join us for an exceptional gathering of leaders and innovators</p>
        </section>
      </main>
    </>
  );
}
