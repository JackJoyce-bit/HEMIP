using System;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Imaging;
using System.IO;

internal static class PrepareHomeImages
{
    private static readonly string SourceRoot = @"C:\Users\Le MADISON\OneDrive\Desktop\HEMIP-main\.work\pdf-assets";
    private static readonly string OutputRoot = @"C:\Users\Le MADISON\OneDrive\Desktop\HEMIP-main\config\assets\img";
    private static readonly string[,] Images = new string[,]
    {
        { "hemip-campus.jpg", "preview-07.jpg" },
        { "hemip-graduation.jpg", "pdf-image-01-658x485.png" },
        { "hemip-atelier.jpg", "pdf-image-02-509x448.png" },
        { "hemip-etudiants.jpg", "pdf-image-03-501x771.png" },
        { "hemip-ceremonie.jpg", "pdf-image-04-1080x810.png" },
        { "hemip-rencontre.jpg", "pdf-image-05-1080x810.png" },
        { "hemip-informatique.jpg", "pdf-image-06-526x358.png" },
        { "hemip-vie-ecole.jpg", "pdf-image-08-1080x810.png" }
    };

    private static void Main()
    {
        ImageCodecInfo jpeg = null;
        foreach (ImageCodecInfo codec in ImageCodecInfo.GetImageEncoders())
        {
            if (codec.MimeType == "image/jpeg") { jpeg = codec; break; }
        }
        if (jpeg == null) throw new InvalidOperationException("Encodeur JPEG indisponible.");

        for (int i = 0; i < Images.GetLength(0); i++)
        {
            string outputName = Images[i, 0];
            string inputPath = Path.Combine(SourceRoot, Images[i, 1]);
            string outputPath = Path.Combine(OutputRoot, outputName);
            if (!File.Exists(inputPath)) throw new FileNotFoundException("Source absente.", inputPath);

            using (Image source = Image.FromFile(inputPath))
            {
                double scale = Math.Min(1.0, 1920.0 / Math.Max(source.Width, source.Height));
                int width = Math.Max(1, (int)Math.Round(source.Width * scale));
                int height = Math.Max(1, (int)Math.Round(source.Height * scale));
                using (Bitmap bitmap = new Bitmap(width, height, PixelFormat.Format24bppRgb))
                using (Graphics graphics = Graphics.FromImage(bitmap))
                using (EncoderParameters parameters = new EncoderParameters(1))
                {
                    graphics.Clear(Color.White);
                    graphics.CompositingQuality = CompositingQuality.HighQuality;
                    graphics.InterpolationMode = InterpolationMode.HighQualityBicubic;
                    graphics.SmoothingMode = SmoothingMode.HighQuality;
                    graphics.DrawImage(source, 0, 0, width, height);
                    parameters.Param[0] = new EncoderParameter(System.Drawing.Imaging.Encoder.Quality, 86L);
                    bitmap.Save(outputPath, jpeg, parameters);
                }
                FileInfo info = new FileInfo(outputPath);
                Console.WriteLine("{0} | {1}x{2} | {3} KB", outputName, width, height, Math.Round(info.Length / 1024.0));
            }
        }
    }
}
